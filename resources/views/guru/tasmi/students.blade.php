@extends('layouts.app')

@section('title', "Tasmi' Al-Quran")

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Tasmi' Al-Quran</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Pilih siswa dan juz, lalu nilai kelancaran, tajwid, dan fasohah pada setiap halaman atau surah.</p>
        </div>
        <a href="{{ route('guru.tasmi.rekap') }}" class="inline-flex items-center gap-2 rounded-lg border border-primary/30 px-4 py-2 text-sm font-medium text-primary transition-colors hover:bg-primary/10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Rekap Tasmi'
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-primary/20 bg-primary/10 p-4 text-sm text-primary">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
            <p class="font-medium">Periksa kembali data Tasmi':</p>
            <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="tasmiForm" method="POST" action="{{ route('guru.tasmi.store') }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414]">
        @csrf
        <input id="studentId" type="hidden" name="student_id" value="{{ old('student_id', $selectedStudent?->id) }}">
        <div class="space-y-6 p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="relative">
                    <label for="studentSearch" class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/50">Nama siswa <span class="text-red-500">*</span></label>
                    <input id="studentSearch" type="search" autocomplete="off" required value="{{ $selectedStudent?->name }}"
                        placeholder="Ketik minimal 3 huruf nama siswa"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                    <ul id="suggestions" class="absolute z-10 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-[#141414]" role="listbox"></ul>
                    <p id="searchHint" class="mt-1 text-xs text-slate-400 dark:text-white/40" aria-live="polite">
                        @if ($selectedStudent)
                            Dipilih: {{ $selectedStudent->name }} — {{ $selectedStudent->classRoom?->class_name ?? 'Tanpa Kelas' }}
                        @else
                            @if ($canAssessAllStudents)
                                Guru Al-Qur’an dan PJ Tahfidz dapat mencari semua murid di sekolah ini.
                            @else
                                Hasil pencarian hanya menampilkan murid yang diampu oleh Anda.
                            @endif
                        @endif
                    </p>
                </div>
                <div>
                    <label for="recordedDate" class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/50">Tanggal Tasmi'</label>
                    <input id="recordedDate" type="date" name="recorded_date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                </div>
            </div>

            <div class="max-w-sm">
                <label for="juzSelect" class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/50">Juz (opsional)</label>
                <select id="juzSelect" name="juz"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                    <option value="">— Pilih Juz —</option>
                    @for ($juz = 1; $juz <= 30; $juz++)
                        <option value="{{ $juz }}" @selected((string) old('juz') === (string) $juz)>Juz {{ $juz }}</option>
                    @endfor
                </select>
            </div>

            <section id="surahSection" class="hidden space-y-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Tasmi' per halaman Mushaf Madinah</h2>
                    <p id="entryModeHint" class="mt-1 text-xs text-slate-400 dark:text-white/40">Rentang surah dan ayat mengikuti pilihan juz.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-white/40">Nilai 0–100: kelancaran bacaan, penerapan tajwid, dan kejelasan pelafalan (fasohah).</p>
                </div>
                <div id="surahRows" class="space-y-3"></div>
            </section>
        </div>
        <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-white/10 dark:bg-white/[0.02]">
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90">Simpan Tasmi'</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const pageMap = @json($pageMap);
    const surahMap = @json($surahMap);
    const oldPages = @json(old('pages', []));
    const oldSurahs = @json(old('surahs', []));
    const canAssessAllStudents = @json($canAssessAllStudents);
    const searchUrl = @json(route('guru.tasmi.search'));
    const searchInput = document.getElementById('studentSearch');
    const studentId = document.getElementById('studentId');
    const hint = document.getElementById('searchHint');
    const suggestions = document.getElementById('suggestions');
    const juzSelect = document.getElementById('juzSelect');
    const section = document.getElementById('surahSection');
    const rows = document.getElementById('surahRows');
    const sectionTitle = section.querySelector('h2');
    const modeHint = document.getElementById('entryModeHint');
    let debounce;
    let requestNumber = 0;

    function clearSuggestions() {
        suggestions.replaceChildren();
        suggestions.classList.add('hidden');
    }

    function makeInput(type, name, value, min, max, required) {
        const input = document.createElement('input');
        input.type = type;
        input.name = name;
        input.value = value;
        input.required = required;
        if (min !== null) input.min = min;
        if (max !== null) input.max = max;
        input.className = 'mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white';
        return input;
    }

    function makeAssessmentField(labelText, control, className) {
        const label = document.createElement('label');
        label.className = 'block min-w-0 text-xs font-medium text-slate-500 dark:text-white/50 ' + (className || '');
        label.append(document.createTextNode(labelText), control);
        return label;
    }

    function renderAssessmentRow(group, index, previous, title, detail, noteLabel, hiddenFields) {
        const card = document.createElement('details');
        card.className = 'group rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414]';

        const heading = document.createElement('summary');
        heading.className = 'flex cursor-pointer list-none items-center justify-between gap-3 p-3 marker:hidden [&::-webkit-details-marker]:hidden';
        const titleText = document.createElement('p');
        titleText.className = 'truncate text-sm font-semibold text-slate-700 dark:text-white/80';
        titleText.textContent = title;
        const detailText = document.createElement('p');
        detailText.className = 'mt-0.5 truncate text-xs text-slate-400 dark:text-white/40';
        detailText.textContent = detail;
        heading.title = title + ' — ' + detail;
        const headingText = document.createElement('div');
        headingText.className = 'min-w-0';
        headingText.append(titleText, detailText);
        const expandIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        expandIcon.setAttribute('class', 'h-4 w-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180');
        expandIcon.setAttribute('fill', 'none');
        expandIcon.setAttribute('viewBox', '0 0 24 24');
        expandIcon.setAttribute('stroke', 'currentColor');
        expandIcon.setAttribute('aria-hidden', 'true');
        const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'm19 9-7 7-7-7');
        expandIcon.appendChild(iconPath);
        heading.append(headingText, expandIcon);
        card.appendChild(heading);

        hiddenFields.forEach(function (field) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = group + '[' + index + '][' + field[0] + ']';
            hidden.value = field[1];
            card.appendChild(hidden);
        });

        const fields = document.createElement('div');
        fields.className = 'overflow-x-auto border-t border-slate-100 p-3 dark:border-white/5';
        const fieldRow = document.createElement('div');
        fieldRow.className = 'grid min-w-[700px] items-end gap-2';
        fieldRow.style.gridTemplateColumns = 'repeat(3, minmax(4.5rem, 0.65fr)) minmax(5rem, 0.7fr) 3.5rem minmax(8rem, 1.3fr)';

        const scores = [
            ['fluency_score', 'Kelancaran'],
            ['tajwid_score', 'Tajwid'],
            ['fasohah_score', 'Fasohah']
        ].map(function (field) {
            const input = makeInput(
                'number',
                group + '[' + index + '][' + field[0] + ']',
                previous ? previous[field[0]] : '',
                0,
                100,
                false
            );
            input.step = '1';
            input.placeholder = '0–100';
            input.setAttribute('aria-label', field[1] + ' ' + title);
            input.className = 'mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white';
            fieldRow.appendChild(makeAssessmentField(field[1], input));
            return input;
        });

        const average = document.createElement('input');
        average.type = 'text';
        average.readOnly = true;
        average.value = '';
        average.placeholder = 'Otomatis';
        average.setAttribute('aria-label', 'Rata-rata ' + title);
        average.className = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm font-semibold text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-white/80';
        fieldRow.appendChild(makeAssessmentField('Rata-rata', average));

        const grade = document.createElement('span');
        const gradeBaseClass = 'mt-1 inline-flex h-9 min-w-8 items-center justify-center rounded-md px-2 text-xs font-bold';
        grade.className = gradeBaseClass + ' bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-white/50';
        grade.textContent = '—';
        fieldRow.appendChild(makeAssessmentField('Grade', grade));

        const notes = makeInput(
            'text',
            group + '[' + index + '][notes]',
            previous ? previous.notes : '',
            null,
            null,
            false
        );
        notes.placeholder = noteLabel;
        notes.setAttribute('aria-label', noteLabel + ' ' + title);
        notes.className = 'mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white';
        fieldRow.appendChild(makeAssessmentField('Catatan', notes));
        fields.appendChild(fieldRow);
        card.appendChild(fields);

        function updateSummary() {
            const values = scores.map(function (input) {
                return input.value.trim();
            });
            if (values.some(function (value) { return value === ''; })) {
                average.value = '';
                grade.textContent = '—';
                grade.className = gradeBaseClass + ' bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-white/50';
                return;
            }

            const numericValues = values.map(Number);
            if (numericValues.some(function (value) {
                return !Number.isInteger(value) || value < 0 || value > 100;
            })) {
                average.value = '';
                grade.textContent = '—';
                grade.className = gradeBaseClass + ' bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-white/50';
                return;
            }

            const averageScore = numericValues.reduce(function (sum, value) {
                return sum + value;
            }, 0) / values.length;
            const roundedScore = Math.round(averageScore);
            const gradeValue = roundedScore >= 90 ? 'A' : roundedScore >= 75 ? 'B' : roundedScore >= 60 ? 'C' : 'D';
            const gradeClasses = {
                A: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                B: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                C: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                D: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400'
            };
            average.value = averageScore.toFixed(1);
            grade.textContent = gradeValue;
            grade.className = gradeBaseClass + ' ' + gradeClasses[gradeValue];
        }

        scores.forEach(function (input) {
            input.addEventListener('input', updateSummary);
        });
        updateSummary();
        rows.appendChild(card);
    }

    function renderPages(juz) {
        rows.replaceChildren();
        rows.className = 'space-y-3';
        const pages = pageMap[juz] || [];
        section.classList.toggle('hidden', pages.length === 0);
        sectionTitle.textContent = 'Tasmi’ per halaman Mushaf Madinah';
        modeHint.textContent = 'Setiap halaman memiliki kartu penilaian. Rata-rata dan grade dihitung otomatis setelah ketiga aspek diisi.';

        pages.forEach(function (page, index) {
            const previous = oldPages.find(function (item) {
                return Number(item.page_number) === page.number;
            });
            const ranges = page.segments.map(function (segment) {
                return segment.number + '. ' + segment.name + ' (' + segment.start + '–' + segment.end + ')';
            }).join(' · ');
            const hiddenFields = [['page_number', page.number]];
            page.segments.forEach(function (segment, segmentIndex) {
                [
                    ['quran_master_id', segment.id],
                    ['ayat_start', segment.start],
                    ['ayat_end', segment.end]
                ].forEach(function (field) {
                    hiddenFields.push(['segments][' + segmentIndex + '][' + field[0], field[1]]);
                });
            });
            renderAssessmentRow('pages', index, previous, 'Halaman ' + page.number, ranges, 'Catatan halaman', hiddenFields);
        });
    }

    function renderSurahs(juz) {
        rows.replaceChildren();
        rows.className = 'space-y-3';
        const surahs = surahMap[juz] || [];
        section.classList.toggle('hidden', surahs.length === 0);
        sectionTitle.textContent = 'Tasmi’ per surah';
        modeHint.textContent = 'Setiap surah memiliki kartu penilaian. Rata-rata dan grade dihitung otomatis setelah ketiga aspek diisi.';

        surahs.forEach(function (surah, index) {
            const previous = oldSurahs.find(function (item) {
                return item.quran_master_id === surah.id;
            });
            renderAssessmentRow(
                'surahs',
                index,
                previous,
                surah.number + '. ' + surah.name,
                'Ayat ' + surah.start + '–' + surah.end,
                'Catatan surah',
                [
                    ['quran_master_id', surah.id],
                    ['ayat_start', surah.start],
                    ['ayat_end', surah.end]
                ]
            );
        });
    }

    function renderJuzEntries(juz) {
        if (!juz) {
            rows.replaceChildren();
            section.classList.add('hidden');
            return;
        }

        if (Number(juz) >= 26) {
            renderSurahs(juz);
            return;
        }

        renderPages(juz);
    }

    juzSelect.addEventListener('change', function () {
        renderJuzEntries(this.value);
    });
    if (juzSelect.value) renderJuzEntries(juzSelect.value);

    document.getElementById('tasmiForm').addEventListener('submit', function (event) {
        if (!studentId.value) {
            event.preventDefault();
            searchInput.setCustomValidity('Pilih nama siswa dari saran pencarian.');
            searchInput.reportValidity();
        }
    });

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        const currentRequest = ++requestNumber;
        studentId.value = '';
        searchInput.setCustomValidity('');
        clearSuggestions();
        clearTimeout(debounce);

        if (query.length < 3) {
            hint.textContent = 'Ketik minimal 3 huruf untuk mencari siswa.';
            return;
        }

        hint.textContent = 'Mencari siswa...';
        debounce = setTimeout(async function () {
            try {
                const url = new URL(searchUrl, window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Pencarian siswa gagal. Silakan coba lagi.');

                const students = await response.json();
                if (currentRequest !== requestNumber) return;
                if (students.length === 0) {
                    hint.textContent = canAssessAllStudents
                        ? 'Murid tidak ditemukan di sekolah ini.'
                        : 'Murid tidak ditemukan atau belum diampu oleh Anda.';
                    return;
                }

                students.forEach(function (student) {
                    const item = document.createElement('li');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'flex w-full items-center justify-between gap-4 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-white/[0.04]';

                    const name = document.createElement('span');
                    name.className = 'text-sm font-medium text-slate-700 dark:text-white/80';
                    name.textContent = student.name;

                    const detail = document.createElement('span');
                    detail.className = 'text-xs text-slate-400 dark:text-white/40';
                    detail.textContent = [student.class ? 'Kelas ' + student.class : '', student.nis || ''].filter(Boolean).join(' · ');

                    button.append(name, detail);
                    button.addEventListener('click', function () {
                        studentId.value = student.id;
                        searchInput.value = student.name;
                        searchInput.setCustomValidity('');
                        hint.textContent = 'Dipilih: ' + student.name + (student.class ? ' — Kelas ' + student.class : '');
                        clearSuggestions();
                    });
                    item.appendChild(button);
                    suggestions.appendChild(item);
                });
                suggestions.classList.remove('hidden');
                hint.textContent = 'Pilih siswa dari hasil pencarian.';
            } catch (error) {
                if (currentRequest === requestNumber) {
                    hint.textContent = error.message || 'Pencarian siswa gagal. Silakan coba lagi.';
                }
            }
        }, 250);
    });
})();
</script>
@endpush
