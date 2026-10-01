@extends('layouts.app')

@section('title', 'Input Nilai — ' . $classRoom->class_name . ' — ' . $subject->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('guru.nilai.index') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary">&larr; Kembali</a>
</div>

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Input Nilai</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $classRoom->class_name }} &middot; {{ $subject->name }} &middot; KKM {{ $kkm }}
        <a href="{{ route('guru.nilai.rekap', ['class_id' => $classRoom->id, 'subject_id' => $subject->id]) }}" class="text-primary dark:text-primary hover:underline">&middot; Atur KKM di Rekap Nilai</a>
    </p>
</div>

<div id="status-banner" class="mb-4 hidden"></div>

<form method="POST" action="{{ route('guru.nilai.store') }}" id="grade-form">
    @csrf
    <input type="hidden" name="class_id" value="{{ $classRoom->id }}">
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tanggal</label>
            <input type="date" id="date-input" name="date" value="{{ $date }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tipe Nilai</label>
            <select id="type-input" name="grade_type_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                @foreach ($gradeTypes as $gt)
                <option value="{{ $gt->id }}" data-weight="{{ $gt->weight }}" {{ $gradeTypeId === $gt->id ? 'selected' : '' }}>{{ $gt->name }} ({{ $gt->weight }}%)</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <div id="ajax-status" class="text-xs text-slate-400 dark:text-white/30 hidden">
                <svg class="inline w-3 h-3 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Memuat...
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
<div class="overflow-x-auto">        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-8">No</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-32">Nilai</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-64">Catatan</th>
                </tr>
            </thead>
            <tbody id="grade-body">
                @foreach ($students as $idx => $student)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-2 text-slate-400 dark:text-white/30 text-xs">{{ $idx + 1 }}</td>
                    <td class="px-4 py-2 font-medium">
                        {{ $student->name }}
                        <input type="hidden" name="nilai[{{ $idx }}][student_id]" value="{{ $student->id }}" data-student-id="{{ $student->id }}">
                    </td>
                    <td class="px-4 py-2">
                        <input type="number" name="nilai[{{ $idx }}][score]"
                               value="{{ $existingGrades[$student->id] ?? '' }}"
                               min="0" max="100" step="0.01"
                               class="score-input w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-center text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80"
                               placeholder="-"
                               data-student-id="{{ $student->id }}">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="nilai[{{ $idx }}][notes]" value=""
                               class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80"
                               placeholder="Opsional">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('guru.nilai.index') }}" class="rounded-lg border border-slate-200 dark:border-white/10 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">Batal</a>
        <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-6 py-2 text-sm font-medium transition-colors">
            Simpan Nilai
        </button>
    </div>
</form>

@php
    $remedialStudents = $students->filter(fn ($st) => isset($gradeRows[$st->id]) && $gradeRows[$st->id]->score !== null && (float) $gradeRows[$st->id]->score < $kkm);
@endphp

@if ($remedialStudents->isNotEmpty())
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Remedial (nilai &lt; KKM {{ $kkm }})</h2>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-0.5">Isi nilai perbaikan. Sistem otomatis membatasi nilai akhir maksimal {{ $kkm }}, sehingga tidak melebihi siswa yang tuntas.</p>
    </div>
    <form method="POST" action="{{ route('guru.nilai.remedial') }}" id="remedial-form">
        @csrf
        <input type="hidden" name="class_id" value="{{ $classRoom->id }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="grade_type_id" value="{{ $gradeTypeId }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-8">No</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Nilai Asli</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-32">Nilai Perbaikan</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Nilai Akhir (cap)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($remedialStudents as $idx => $student)
                    @php $row = $gradeRows[$student->id]; @endphp
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                        <td class="px-4 py-2 text-slate-400 dark:text-white/30 text-xs">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 font-medium">
                            {{ $student->name }}
                            <input type="hidden" name="remedial[{{ $idx }}][student_id]" value="{{ $student->id }}">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-2 py-0.5 text-xs font-bold text-red-600 dark:text-red-400">{{ $row->score }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" name="remedial[{{ $idx }}][score]"
                                   value="{{ $row->remedial_score ?? '' }}"
                                   min="0" max="100" step="0.01"
                                   class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-center text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80"
                                   placeholder="-">
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if ($row->hasRemedial())
                            <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-2 py-0.5 text-xs font-bold text-purple-700 dark:text-purple-400">{{ $row->remedial_capped }}</span>
                            <span class="text-[10px] text-slate-400 dark:text-white/30 ml-1">({{ $row->score }} → {{ $row->remedial_capped }})</span>
                            @else
                            <span class="text-xs text-slate-300 dark:text-white/15">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-end gap-3">
            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-6 py-2 text-sm font-medium transition-colors">
                Simpan Remedial
            </button>
        </div>
    </form>
</div>
@elseif ($gradeTypeId && $hasSubmitted)
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 text-center text-sm text-slate-400 dark:text-white/30">
    Tidak ada siswa dengan nilai di bawah KKM {{ $kkm }} pada penilaian ini.
</div>
@endif

@endsection

@push('scripts')
<script>
(function() {
    const classId = '{{ $classRoom->id }}';
    const subjectId = '{{ $subject->id }}';
    const ajaxUrl = '{{ route("guru.nilai.ajax") }}';
    const dateInput = document.getElementById('date-input');
    const typeInput = document.getElementById('type-input');
    const statusBanner = document.getElementById('status-banner');
    const ajaxStatus = document.getElementById('ajax-status');
    const form = document.getElementById('grade-form');
    let debounceTimer = null;
    let isDirty = false;

    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', () => { isDirty = true; });
    });

    function showBanner(type, msg) {
        statusBanner.className = 'mb-4 rounded-lg p-4 text-sm border ' +
            (type === 'success'
                ? 'bg-primary/10 dark:bg-primary/100/10 border-primary/20 dark:border-primary/20 text-primary dark:text-primary'
                : type === 'info'
                ? 'bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400'
                : 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-400');
        statusBanner.textContent = msg;
        statusBanner.classList.remove('hidden');
    }

    function hideBanner() { statusBanner.classList.add('hidden'); }

    function fetchGrades() {
        const date = dateInput.value;
        const gradeTypeId = typeInput.value;
        if (!date || !gradeTypeId) return;

        ajaxStatus.classList.remove('hidden');
        const params = new URLSearchParams({ class_id: classId, subject_id: subjectId, date: date, grade_type_id: gradeTypeId });

        fetch(ajaxUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            ajaxStatus.classList.add('hidden');
            document.querySelectorAll('.score-input').forEach(input => {
                const sid = input.dataset.studentId;
                input.value = data.grades[sid] ?? '';
            });
            isDirty = false;

            if (data.hasData) {
                showBanner('success', 'Data nilai sudah ada. Mengubah akan menimpa data sebelumnya.');
            } else {
                showBanner('info', 'Belum ada nilai untuk tanggal ini. Silakan isi nilai.');
            }
        })
        .catch(() => { ajaxStatus.classList.add('hidden'); });
    }

    dateInput.addEventListener('change', function() {
        if (isDirty && !confirm('Anda memiliki perubahan yang belum disimpan. Tetap ganti tanggal?')) {
            this.value = this.dataset.previousValue || this.value;
            return;
        }
        this.dataset.previousValue = this.value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchGrades, 300);
    });

    typeInput.addEventListener('change', function() {
        if (isDirty && !confirm('Anda memiliki perubahan yang belum disimpan. Tetap ganti tipe?')) {
            this.dataset.previousValue = this.value;
            return;
        }
        this.dataset.previousValue = this.value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchGrades, 300);
    });

    window.addEventListener('beforeunload', function(e) {
        if (isDirty) { e.preventDefault(); e.returnValue = ''; }
    });

    form.addEventListener('submit', function() { isDirty = false; });
})();
</script>
@endpush
