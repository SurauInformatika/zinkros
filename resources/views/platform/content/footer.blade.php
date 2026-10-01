@extends('layouts.app')

@section('title', 'Footer')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Footer</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola teks footer, kontak, tautan media sosial, dan toggle tampilan pada bagian bawah halaman.</p>
</div>

@include('platform.partials.form-alert')

<form method="POST" action="{{ route('platform.content.footer.update') }}" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <label class="mb-5 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span>
                <span class="block text-sm font-semibold text-slate-700 dark:text-white/70">Tampilkan di landing page</span>
                <span class="block text-xs text-slate-500 dark:text-white/40 mt-0.5">Jika dinonaktifkan, seluruh bagian footer disembunyikan dari halaman beranda dan blog.</span>
            </span>
            <input type="checkbox" name="show_footer" value="1" @checked(old('show_footer', (bool) $settings->show_footer))
                class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
        </label>

        <div class="space-y-3 mb-6">
            <div>
                <label for="footer_tagline" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tagline</label>
                <textarea id="footer_tagline" name="footer_tagline" rows="2" maxlength="300"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y @error('footer_tagline') border-red-400 @enderror">{{ old('footer_tagline', $settings->footer_tagline) }}</textarea>
            </div>
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            <div>
                <label for="footer_address" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Alamat</label>
                <input id="footer_address" type="text" name="footer_address" value="{{ old('footer_address', $settings->footer_address) }}" maxlength="300"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('footer_address') border-red-400 @enderror">
            </div>
            <div>
                <label for="footer_phone" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Telepon</label>
                <input id="footer_phone" type="text" name="footer_phone" value="{{ old('footer_phone', $settings->footer_phone) }}" maxlength="50"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('footer_phone') border-red-400 @enderror">
            </div>
            <div class="sm:col-span-2">
                <label for="footer_email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                <input id="footer_email" type="email" name="footer_email" value="{{ old('footer_email', $settings->footer_email) }}" maxlength="150"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('footer_email') border-red-400 @enderror">
            </div>
            <div>
                <label for="footer_copyright" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Teks Copyright</label>
                <input id="footer_copyright" type="text" name="footer_copyright" value="{{ old('footer_copyright', $settings->footer_copyright) }}" maxlength="200" placeholder="Kosongkan untuk teks default (© Tahun Nama Aplikasi)"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('footer_copyright') border-red-400 @enderror">
            </div>
        </div>

        @php
            $oldMedsos = old('footer_medsos');
            $oldMedsosList = is_array($oldMedsos) ? array_values($oldMedsos) : null;
            $decodedMedsos = json_decode($settings->footer_medsos ?? '', true);
            $medsosSource = $oldMedsosList ?? (is_array($decodedMedsos) && $decodedMedsos !== [] ? $decodedMedsos : config('platform.footer_medsos_defaults'));
            $medsosCount = count($medsosSource);
            $medsosRows = [];
            for ($i = 0; $i < $medsosCount; $i++) {
                $medsosRows[] = [
                    'index' => $i,
                    'icon' => $oldMedsosList[$i]['icon'] ?? $medsosSource[$i]['icon'] ?? '',
                    'label' => $oldMedsosList[$i]['label'] ?? $medsosSource[$i]['label'] ?? '',
                    'url' => $oldMedsosList[$i]['url'] ?? $medsosSource[$i]['url'] ?? '',
                ];
            }
            $medsosIconPath = config('platform.medsos_icons');
        @endphp

        <div class="mb-1 mt-8 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Media Sosial</h2>
        </div>

        <div id="medsos-rows" class="space-y-4 mt-4">
            @foreach ($medsosRows as $medsos)
                @include('platform.partials.medsos-row', ['medsos' => $medsos, 'index' => $medsos['index'], 'medsosIconPath' => $medsosIconPath])
            @endforeach
        </div>

        <button type="button" id="medsos-add"
            class="mt-4 inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-300 dark:border-white/15 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-white/50 transition hover:border-primary hover:text-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Media Sosial
        </button>
        @error('footer_medsos')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const medsosRows = document.getElementById('medsos-rows');
        const medsosAdd = document.getElementById('medsos-add');
        const iconOptions = @json(config('platform.medsos_icons'));

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : str;
            return div.innerHTML;
        }

        function nextMedsosIndex() {
            let max = -1;
            medsosRows.querySelectorAll('[name^="footer_medsos["]').forEach((el) => {
                const m = el.name.match(/\[(\d+)\]\[/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function iconSelectHtml(index, selected = '') {
            let opts = '';
            for (const key in iconOptions) {
                if (Object.prototype.hasOwnProperty.call(iconOptions, key)) {
                    const label = key.charAt(0).toUpperCase() + key.slice(1);
                    opts += '<option value="' + escapeHtml(key) + '"' + (key === selected ? ' selected' : '') + '>' + escapeHtml(label) + '</option>';
                }
            }
            return '<select name="footer_medsos[' + index + '][icon]" class="w-40 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' + opts + '</select>';
        }

        function medsosRowHtml(index, m = { icon: 'instagram', label: '', url: '' }) {
            return '<div class="medsos-row rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4">' +
                '<div class="flex items-start justify-between gap-3">' +
                    '<div class="flex flex-1 flex-wrap items-start gap-3">' +
                        '<div class="flex items-center gap-2">' +
                            '<svg class="h-8 w-8 shrink-0 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="' + (iconOptions[m.icon] || '') + '"/></svg>' +
                            '<div>' +
                                '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Platform</label>' +
                                iconSelectHtml(index, m.icon) +
                            '</div>' +
                        '</div>' +
                        '<div class="flex-1 min-w-[160px]">' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Label</label>' +
                            '<input type="text" name="footer_medsos[' + index + '][label]" maxlength="100" placeholder="Instagram" value="' + escapeHtml(m.label) + '" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                        '</div>' +
                        '<div class="flex-1 min-w-[200px]">' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">URL</label>' +
                            '<input type="text" name="footer_medsos[' + index + '][url]" maxlength="300" placeholder="https://instagram.com/namaakun" value="' + escapeHtml(m.url) + '" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="medsos-remove mt-6 rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus tautan media sosial">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
                    '</button>' +
                '</div>' +
            '</div>';
        }

        function renumberMedsosRows() {
            medsosRows.querySelectorAll('.medsos-row').forEach((row, idx) => {
                row.querySelectorAll('[name^="footer_medsos["]').forEach((el) => {
                    el.name = el.name.replace(/^footer_medsos\[\d+\]/, 'footer_medsos[' + idx + ']');
                });
            });
        }

        medsosAdd.addEventListener('click', () => {
            medsosRows.insertAdjacentHTML('beforeend', medsosRowHtml(nextMedsosIndex()));
        });

        medsosRows.addEventListener('click', (e) => {
            const btn = e.target.closest('.medsos-remove');
            if (btn) {
                btn.closest('.medsos-row').remove();
                renumberMedsosRows();
            }
        });
    });
</script>
@endsection