@extends('layouts.app')

@section('title', 'Assignment Guru & Siswa Al-Quran')

@section('content')
@php $qaPrefix = str(\Illuminate\Support\Facades\Route::currentRouteName())->beforeLast('.')->toString(); @endphp
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Assignment Guru & Siswa Al-Quran</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Tentukan siswa diajar oleh guru Al-Quran siapa</p>
    </div>

    {{-- Tambah Assignment --}}
    <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
        <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Tambah Assignment</h2>
        </div>
        <div class="p-4">
            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Guru Al-Quran</label>
                <select id="teacherSelect" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                    <option value="">Pilih Guru</option>
                    @foreach ($quranTeachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4 relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="searchInput" placeholder="Ketik nama siswa..." class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm placeholder-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white dark:placeholder-white/30">
            </div>
            <div id="selectedBar" class="mb-3 hidden flex items-center justify-between rounded-lg bg-primary/10 px-3 py-2 dark:bg-primary/100/10">
                <span class="text-sm font-medium text-primary dark:text-primary"><span id="selectedCount">0</span> siswa dipilih</span>
                <button type="button" onclick="clearSelection()" class="text-xs text-primary hover:text-primary-dark dark:text-primary">Bersihkan</button>
            </div>
            <div id="classList" class="max-h-[480px] overflow-y-auto space-y-2">
                @forelse ($studentsByClass as $classGroup)
                    <div class="class-group" data-class="{{ strtolower($classGroup['class_name']) }}">
                        <button type="button" onclick="toggleClass(this)" class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-left text-sm font-medium hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-slate-400 transition-transform class-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                <span>{{ $classGroup['class_name'] }}</span>
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-white/10 dark:text-white/40 class-count">{{ $classGroup['students']->count() }}</span>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); toggleClassAll(this)" class="text-xs text-primary hover:text-primary-dark dark:text-primary toggle-all-btn">Pilih semua</button>
                        </button>
                        <div class="class-students hidden mt-1 space-y-0.5 pl-6">
                            @foreach ($classGroup['students'] as $student)
                                <label class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/[0.02] student-row {{ $student['is_assigned'] ? 'assigned opacity-50' : '' }}" data-name="{{ strtolower($student['name']) }}">
                                    @if ($student['is_assigned'])
                                        <span class="flex h-4 w-4 items-center justify-center rounded border border-slate-300 dark:border-white/10 bg-slate-100 dark:bg-white/5">
                                            <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="text-slate-500 dark:text-white/40">{{ $student['name'] }}</span>
                                        <span class="ml-auto text-xs text-slate-400 dark:text-white/30">{{ $student['assigned_teacher'] }}</span>
                                    @else
                                        <input type="checkbox" name="student_ids[]" value="{{ $student['id'] }}" class="student-check h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary dark:border-white/20">
                                        <span class="text-slate-700 dark:text-white/80">{{ $student['name'] }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada siswa yang tersedia.</p>
                @endforelse
            </div>
            <div class="mt-4 flex justify-end border-t border-slate-200 dark:border-white/10 pt-4">
                <form id="bulkForm" method="POST" action="{{ route($qaPrefix . '.bulk-assign') }}">
                    @csrf
                    <div id="hiddenInputs"></div>
                    <input type="hidden" name="teacher_id" id="hiddenTeacher" value="">
                    <button type="submit" id="submitBtn" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed" disabled>Assign Siswa</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Assignment per Guru --}}
    @foreach ($quranTeachers as $teacher)
        @php $teacherAssignments = $assignments->get($teacher->id, collect()); @endphp
        <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">{{ $teacher->name }}</h2>
                <span class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary dark:bg-primary/100/10 dark:text-primary">{{ $teacherAssignments->count() }} siswa</span>
            </div>
            @if ($teacherAssignments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-white/5">
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Kelas</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teacherAssignments as $i => $a)
                                <tr class="border-b border-slate-50 last:border-0 dark:border-white/5">
                                    <td class="px-4 py-2 text-slate-400 dark:text-white/30">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-slate-700 dark:text-white/80">{{ $a->student?->name ?? '-' }}</td>
                                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $a->student->classRoom?->class_name ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route($qaPrefix . '.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus assignment ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-4 py-6 text-center text-xs text-slate-400 dark:text-white/30">Belum ada siswa yang di-assign</p>
            @endif
        </div>
    @endforeach
</div>
@endsection
@push('scripts')
<script>
function toggleClass(btn) {
    const group = btn.closest('.class-group');
    const studentsDiv = group.querySelector('.class-students');
    const chevron = group.querySelector('.class-chevron');
    studentsDiv.classList.toggle('hidden');
    chevron.style.transform = studentsDiv.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

function toggleClassAll(btn) {
    const group = btn.closest('.class-group');
    const checkboxes = group.querySelectorAll('.student-check');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => {
        if (!cb.closest('.student-row').classList.contains('assigned')) {
            cb.checked = !allChecked;
            cb.dispatchEvent(new Event('change'));
        }
    });
    btn.textContent = allChecked ? 'Pilih semua' : 'Batalkan';
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const selectedBar = document.getElementById('selectedBar');
    const selectedCount = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    const hiddenInputs = document.getElementById('hiddenInputs');
    const hiddenTeacher = document.getElementById('hiddenTeacher');
    const teacherSelect = document.getElementById('teacherSelect');

    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.class-group').forEach(group => {
            let visibleCount = 0;
            group.querySelectorAll('.student-row').forEach(row => {
                const match = row.dataset.name.includes(q);
                row.style.display = match ? '' : 'none';
                if (match && !row.classList.contains('assigned')) visibleCount++;
            });
            group.querySelector('.class-count').textContent = visibleCount;
            const anyVisible = group.querySelectorAll('.student-row:not([style*="none"])').length > 0;
            group.style.display = (q && !anyVisible) ? 'none' : '';
            if (q && anyVisible) {
                group.querySelector('.class-students').classList.remove('hidden');
                group.querySelector('.class-chevron').style.transform = 'rotate(180deg)';
            }
        });
    });

    function updateSelection() {
        const checked = document.querySelectorAll('.student-check:checked');
        const count = checked.length;
        selectedCount.textContent = count;
        selectedBar.classList.toggle('hidden', count === 0);
        submitBtn.disabled = count === 0 || !teacherSelect.value;
        hiddenInputs.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = cb.value;
            hiddenInputs.appendChild(input);
        });
    }

    document.querySelectorAll('.student-check').forEach(cb => cb.addEventListener('change', updateSelection));
    teacherSelect.addEventListener('change', function() {
        hiddenTeacher.value = this.value;
        updateSelection();
    });

    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        if (!teacherSelect.value) {
            e.preventDefault();
            teacherSelect.focus();
        }
    });
});

function clearSelection() {
    document.querySelectorAll('.student-check:checked').forEach(cb => {
        cb.checked = false;
        cb.dispatchEvent(new Event('change'));
    });
}
</script>
@endpush