@php
    $selectedParents = [];
    $primaryIndex = 0;

    if (count(old('parent_user_ids', [])) > 0) {
        foreach (old('parent_user_ids') as $i => $pid) {
            if (!$pid) continue;
            $selectedParents[] = [
                'index' => $i,
                'id' => $pid,
                'name' => old('parent_names')[$i] ?? 'Orang Tua',
                'relation' => old('parent_relations')[$i] ?? 'WALI',
                'is_primary' => old('primary_parent') !== null && (int) old('primary_parent') === $i,
            ];
            if (old('primary_parent') !== null && (int) old('primary_parent') === $i) {
                $primaryIndex = $i;
            }
        }
    } elseif (isset($student) && $student->exists) {
        $selectedParents = $student->parents()->orderBy('pivot_is_primary', 'desc')->get()->map(function ($p, $i) {
            return [
                'index' => $i,
                'id' => $p->id,
                'name' => $p->name,
                'relation' => $p->pivot->relation,
                'is_primary' => (bool) $p->pivot->is_primary,
            ];
        })->values()->toArray();
        $primaryIndex = collect($selectedParents)->firstWhere('is_primary')['index'] ?? 0;
    }
@endphp

<div>
    <span class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Orang Tua / Wali <span class="text-slate-400">(opsional, bisa lebih dari satu)</span></span>

    <div id="selected-parents" class="space-y-2">
        @foreach ($selectedParents as $sp)
            @include('admin.student._parent-selected', [
                'idx' => $sp['index'],
                'id' => $sp['id'],
                'name' => $sp['name'],
                'relation' => $sp['relation'],
                'isPrimary' => $sp['is_primary'],
                'primaryIndex' => $primaryIndex,
            ])
        @endforeach
    </div>

    <div class="mt-3 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50/70 dark:bg-white/5 p-3">
        <button type="button" onclick="openParentSearch()" id="open-parent-search-btn"
            class="inline-flex items-center gap-1.5 text-sm text-primary dark:text-primary hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Tambah Ortu / Wali
        </button>

        <div id="parent-search-box" class="hidden mt-2">
            <input type="text" id="parent-search-input"
                placeholder="Ketik nama, email, atau telepon ortu..."
                autocomplete="off"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            <div id="parent-search-results" class="mt-2 space-y-1 max-h-60 overflow-y-auto"></div>

            <div id="parent-create-box" class="hidden mt-3 border-t border-slate-200 dark:border-white/10 pt-3">
                <p class="text-xs font-medium text-slate-500 dark:text-white/50 mb-2">
                    Tidak ditemukan akun yang cocok. Buat akun baru:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <input type="text" id="new-parent-name" placeholder="Nama lengkap" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <input type="email" id="new-parent-email" placeholder="Email" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <input type="text" id="new-parent-phone" placeholder="Telepon (opsional)" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                </div>
                <p id="new-parent-error" class="mt-2 text-xs text-red-600 hidden"></p>
                <button type="button" onclick="createParent()"
                    class="mt-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark transition">Buat &amp; hubungkan</button>
            </div>
        </div>
    </div>

    @error('parent_user_ids')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

@push('scripts')
<script>
    let parentSearchDebounce = null;

    function reindexParentRows() {
        const rows = Array.from(document.querySelectorAll('#selected-parents .parent-selected'));
        rows.forEach((row, i) => {
            row.dataset.idx = i;
            row.querySelectorAll('input[name="parent_user_ids[]"]').forEach(inp => (inp.name = 'parent_user_ids[]'));
            const radio = row.querySelector('input[name="primary_parent"]');
            if (radio) radio.value = i;
        });
        // guarantee at most one primary; set first if none
        let any = rows.some(r => r.querySelector('input[name="primary_parent"]')?.checked);
        if (!any && rows.length > 0) {
            rows[0].querySelector('input[name="primary_parent"]').checked = true;
        }
        refreshRelationOptions();
    }

    // Business rule: max 1 Ayah and 1 Ibu per student.
    function refreshRelationOptions() {
        const rows = Array.from(document.querySelectorAll('#selected-parents .parent-selected'));
        const taken = {};
        rows.forEach((row, i) => {
            const sel = row.querySelector('select[name="parent_relations[]"]');
            if (sel) {
                sel.querySelectorAll('option[value="AYAH"], option[value="IBU"]').forEach(o => (o.disabled = false));
                const v = sel.value;
                if (v === 'AYAH' || v === 'IBU') taken[v] = (taken[v] || 0) + 1;
            }
        });
        rows.forEach((row) => {
            const sel = row.querySelector('select[name="parent_relations[]"]');
            if (!sel) return;
            if (taken['AYAH'] > 1) {
                row.querySelectorAll('option[value="AYAH"]').forEach(o => {
                    if (sel.value !== 'AYAH') o.disabled = true;
                });
            }
            if (taken['IBU'] > 1) {
                row.querySelectorAll('option[value="IBU"]').forEach(o => {
                    if (sel.value !== 'IBU') o.disabled = true;
                });
            }
        });
    }

    function openParentSearch() {
        const box = document.getElementById('parent-search-box');
        box.classList.remove('hidden');
        document.getElementById('parent-search-input').focus();
    }

    function closeParentSearch() {
        document.getElementById('parent-search-box').classList.add('hidden');
        document.getElementById('parent-search-results').innerHTML = '';
        document.getElementById('parent-create-box').classList.add('hidden');
        document.getElementById('parent-search-input').value = '';
    }

    document.getElementById('parent-search-input').addEventListener('input', function () {
        clearTimeout(parentSearchDebounce);
        parentSearchDebounce = setTimeout(() => searchParents(this.value), 250);
    });

    function searchParents(q) {
        const resultsEl = document.getElementById('parent-search-results');
        const createBox = document.getElementById('parent-create-box');

        fetch('{{ route('admin.siswa.ortu.search') }}' + '?q=' + encodeURIComponent(q || ''), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(json => {
            const items = json.data || [];
            resultsEl.innerHTML = '';
            createBox.classList.add('hidden');

            if (items.length === 0) {
                resultsEl.innerHTML = '<p class="text-sm text-slate-500 dark:text-white/50 px-2 py-1">Tidak ditemukan akun yang cocok.</p>';
                // prefill create form with the typed name
                document.getElementById('new-parent-name').value = q || '';
                createBox.classList.remove('hidden');
                return;
            }

            items.forEach(u => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-white dark:hover:bg-white/5 border border-transparent cursor-pointer';
                div.innerHTML = `
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">${escapeHtml(u.name)}</p>
                        <p class="text-xs text-slate-500 dark:text-white/50 truncate">${escapeHtml(u.email || '')}${u.children_count ? ' · ' + u.children_count + ' anak' : ''}</p>
                    </div>
                    <button type="button" class="shrink-0 text-primary dark:text-primary text-xs font-semibold hover:underline">Pilih</button>`;
                div.querySelector('button').addEventListener('click', () => addSelectedParent(u.id, u.name));
                resultsEl.appendChild(div);
            });
        })
        .catch(() => {
            resultsEl.innerHTML = '<p class="text-sm text-red-600 px-2 py-1">Gagal memuat data.</p>';
        });
    }

    function addSelectedParent(id, name) {
        const holder = document.createElement('div');
        holder.className = 'parent-selected';
        holder.innerHTML = `
            <input type="hidden" name="parent_user_ids[]" value="${id}">
            <input type="hidden" name="parent_names[]" value="${escapeAttr(name)}">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50/70 dark:bg-white/5">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">${escapeHtml(name)}</p>
                </div>
                <select name="parent_relations[]" onchange="refreshRelationOptions()" class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <option value="AYAH">Ayah</option>
                    <option value="IBU">Ibu</option>
                    <option value="WALI" selected>Wali</option>
                </select>
                <label class="inline-flex items-center whitespace-nowrap">
                    <input type="radio" name="primary_parent" value="0" class="rounded-full border-slate-300 text-primary focus:ring-primary">
                    <span class="ml-1 text-xs text-slate-600 dark:text-white/50">Utama</span>
                </label>
                <button type="button" onclick="removeSelectedParent(this)" class="text-red-400 hover:text-red-600 text-base leading-none">&times;</button>
            </div>`;
        document.getElementById('selected-parents').appendChild(holder);
        reindexParentRows();
        closeParentSearch();
    }

    function createParent() {
        const name = document.getElementById('new-parent-name').value.trim();
        const email = document.getElementById('new-parent-email').value.trim();
        const phone = document.getElementById('new-parent-phone').value.trim();
        const errEl = document.getElementById('new-parent-error');
        errEl.classList.add('hidden');

        if (!name || !email) {
            errEl.textContent = 'Nama dan email wajib diisi.';
            errEl.classList.remove('hidden');
            return;
        }

        fetch('{{ route('admin.siswa.ortu.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ name, email, phone })
        })
        .then(r => r.json())
        .then(json => {
            if (json.error) {
                const msg = json.error.email ? json.error.email[0] : 'Terjadi kesalahan.';
                errEl.textContent = msg;
                errEl.classList.remove('hidden');
                return;
            }
            addSelectedParent(json.data.id, json.data.name);
            document.getElementById('new-parent-email').value = '';
            document.getElementById('new-parent-phone').value = '';
        })
        .catch(() => {
            errEl.textContent = 'Gagal membuat akun.';
            errEl.classList.remove('hidden');
        });
    }

    function removeSelectedParent(btn) {
        btn.closest('.parent-selected').remove();
        reindexParentRows();
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function escapeAttr(s) {
        return escapeHtml(s).replace(/"/g, '&quot;');
    }
</script>
@endpush
