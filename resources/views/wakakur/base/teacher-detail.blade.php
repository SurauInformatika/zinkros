@extends('layouts.app')

@section('title', 'Detail Guru')

@section('content')
@php
    $showPlotting = $plotting->count() > 0;
    $showWali = $guru->waliClasses->count() > 0;
    $showPrioritas = $prioritySubjects->count() > 0;
    $showTugas = $guru->teacherRoles->count() > 0;
    $showTahfidz = (bool) $isTahfidzTeacher;
    $rightVisible = $showWali || $showPrioritas || $showTugas || $showTahfidz;
    $needsSetup = ! ($showPlotting && $showWali && $showPrioritas && $showTugas && $showTahfidz);
@endphp
<a href="{{ route($routeGroup . '.teachers') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar guru</a>

<div class="mt-4 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <div class="flex flex-wrap items-center gap-5">
        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-xl font-bold text-white shrink-0">
            {{ strtoupper(mb_substr($guru->name, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold tracking-tight">{{ $guru->name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $guru->email }}@if ($guru->phone) &middot; {{ $guru->phone }}@endif</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @if ($guru->gender === 'L')
                    <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">Laki-laki</span>
                @elseif ($guru->gender === 'P')
                    <span class="inline-flex items-center rounded-md bg-pink-50 dark:bg-pink-500/10 px-2 py-0.5 text-xs font-medium text-pink-700 dark:text-pink-400">Perempuan</span>
                @endif
                @if ($guru->position)
                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-600 dark:text-white/50">{{ $guru->position }}</span>
                @endif
                @if ($guru->createdBy)
                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-600 dark:text-white/50">Ditambahkan oleh {{ $guru->createdBy->name }}</span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($guru->waliClasses->count() > 0)
                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">Wali Kelas ({{ $guru->waliClasses->count() }})</span>
            @endif
            @if ($plotting->count() > 0)
                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">Guru Mapel ({{ $plotting->count() }})</span>
            @endif
            @if ($guru->isQuranTeacher())
                <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-700 dark:text-purple-400">Guru Al-Quran</span>
            @endif
            @if ($needsSetup)
                <button type="button" id="atur-tugas-btn" onclick="revealMissingTasks(this)"
                    class="ml-2 inline-flex items-center rounded-lg bg-gradient-to-r from-primary to-secondary px-3.5 py-1.5 text-xs font-semibold text-white shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                    Atur Tugas
                </button>
            @endif
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2" id="task-grid">
    <div data-task="plotting"
        class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden task-card @if ($showPlotting && ! $rightVisible) lg:col-span-2 @endif"
        @unless ($showPlotting) hidden @endunless>
        <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Plotting Mengajar</h2>
                @if ($activeYear)
                    <p class="text-xs text-slate-400 dark:text-white/30 mt-0.5">Tahun ajaran aktif: {{ $activeYear }}</p>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <form method="POST" action="{{ route($routeGroup . '.plotting.destroy-many', $guru) }}">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-between gap-3 px-6 py-2.5 border-b border-slate-100 dark:border-white/5">
                    <p class="text-xs text-slate-400 dark:text-white/30">
                        @if ($errors->has('plot_ids'))
                            <span class="text-red-600 dark:text-red-400">{{ $errors->first('plot_ids') }}</span>
                        @else
                            Centang kelas lalu hapus banyak sekaligus.
                        @endif
                    </p>
                    <button id="plot-batch-btn" type="submit" disabled
                        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-500 disabled:opacity-40 disabled:cursor-not-allowed">
                        Hapus terpilih (0)
                    </button>
                </div>
<div class="overflow-x-auto">                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" id="plot_master" onclick="togglePlotAll(this)"
                                    class="rounded border-slate-300 text-primary focus:ring-primary">
                            </th>
                            <th class="px-6 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tipe</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plotting as $item)
                        <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                            <td class="px-4 py-2.5">
                                <input type="checkbox" name="plot_ids[]" value="{{ $item->id }}"
                                    class="plot-del rounded border-slate-300 text-primary focus:ring-primary">
                            </td>
                            <td class="px-6 py-2.5 font-medium">{{ $item->classRoom?->class_name }}</td>
                            <td class="px-6 py-2.5">{{ $item->subject?->name }}</td>
                            <td class="px-6 py-2.5">
                                @if ($item->subject?->isQuran())
                                    <span class="text-xs text-purple-600 dark:text-purple-400">Quran</span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-white/30">Umum</span>
                                @endif
                            </td>
                            <td class="px-6 py-2.5 text-right">
                                <button type="submit"
                                    formaction="{{ route($routeGroup . '.plotting.destroy', [$guru, $item]) }}"
                                    onclick="return confirm('Hapus plotting kelas {{ $item->classRoom?->class_name }} ini?')"
                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 dark:text-white/30">Belum ada plotting.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </form>
        </div>
        <form method="POST" action="{{ route($routeGroup . '.plotting.store', $guru) }}" class="px-6 py-4 border-t border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
            @csrf
            <p class="text-xs font-medium text-slate-500 dark:text-white/40 mb-2">Tambah plotting</p>
            @if ($errors->any() && $errors->has('class_ids'))
                <p class="text-xs text-red-600 dark:text-red-400 mb-2">{{ $errors->first('class_ids') }}</p>
            @endif
            <div class="flex flex-wrap items-end gap-2">
                <div class="min-w-[200px] flex-1">
                    <label class="block text-xs text-slate-500 dark:text-white/40 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <div class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] p-3 max-h-44 overflow-y-auto text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="chk_all" onclick="togglePlotClasses(this)"
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="ml-2 font-medium text-slate-700 dark:text-white/70">Pilih semua ({{ $allClasses->count() }} kelas)</span>
                        </label>
                        <div class="my-2 border-t border-slate-200 dark:border-white/10"></div>
                        @foreach ($allClasses as $class)
                            <label class="flex items-center py-0.5 cursor-pointer">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                                    class="plot-class rounded border-slate-300 text-primary focus:ring-primary">
                                <span class="ml-2 text-slate-600 dark:text-white/50">{{ $class->class_name }}</span>
                            </label>
                        @endforeach
                        @if ($allClasses->isEmpty())
                            <p class="text-xs text-slate-400 dark:text-white/30">Belum ada kelas.</p>
                        @endif
                    </div>
                    @error('class_ids')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="min-w-[140px] flex-1">
                    <label for="subject_id" class="block text-xs text-slate-500 dark:text-white/40 mb-1">Mata Pelajaran</label>
                    <select id="subject_id" name="subject_id"
                        class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                        <option value="">Pilih mapel&hellip;</option>
                        @foreach ($allSubjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="rounded-lg bg-gradient-to-r from-primary to-secondary px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Tambah
                </button>
            </div>
        </form>
    </div>

    <div data-task="stack" class="space-y-6 @if (! $showPlotting) lg:col-span-2 @endif"
        @unless ($rightVisible) hidden @endunless>
        <div data-task="wali" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 task-card"
            @unless ($showWali) hidden @endunless>
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Wali Kelas</h2>
                <button type="button" data-ctl="kelola-wali"
                    onclick="toggleKelola('kelola-wali')"
                    class="text-xs font-medium text-primary dark:text-primary hover:underline">Kelola</button>
            </div>
            <div class="px-6 py-4">
                @forelse ($guru->waliClasses as $kelas)
                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-sm font-medium text-slate-600 dark:text-white/50 mr-1 mb-1">{{ $kelas->class_name }}</span>
                @empty
                    <p class="text-sm text-slate-400 dark:text-white/30">Tidak menjadi wali kelas.</p>
                @endforelse

                <form method="POST" action="{{ route($routeGroup . '.wali.update', $guru) }}" id="kelola-wali" class="hidden mt-4 border-t border-slate-100 dark:border-white/5 pt-4">
                    @csrf
                    @method('PUT')
                    @error('class_id')
                        <p class="text-xs text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
                    @enderror
                    <label for="class_id" class="block text-xs text-slate-500 dark:text-white/40 mb-1">Pilih kelas (maksimal 2 wali per kelas)</label>
                    <div class="flex flex-wrap items-end gap-2">
                        <select id="class_id" name="class_id"
                            class="flex-1 min-w-[140px] rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="">Lepas wali kelas</option>
                            @foreach ($allClasses as $class)
                                <option value="{{ $class->id }}" {{ $guru->waliClasses->contains('id', $class->id) ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="rounded-lg bg-gradient-to-r from-primary to-secondary px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div data-task="prioritas" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 task-card"
            @unless ($showPrioritas) hidden @endunless>
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Mapel Prioritas</h2>
                <button type="button" data-ctl="kelola-prioritas"
                    onclick="toggleKelola('kelola-prioritas')"
                    class="text-xs font-medium text-primary dark:text-primary hover:underline">Kelola</button>
            </div>
            <div class="px-6 py-4">
                <div class="flex flex-wrap">
                    @forelse ($prioritySubjects as $subject)
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-sm font-medium text-slate-600 dark:text-white/50 mr-1 mb-1">
                            {{ $subject->name }}
                            @if ($subject->isQuran())
                                <span class="ml-1 text-xs text-purple-600 dark:text-purple-400">(Quran)</span>
                            @endif
                        </span>
                    @empty
                        <p class="text-sm text-slate-400 dark:text-white/30">Tidak ada mapel prioritas.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route($routeGroup . '.prioritas.update', $guru) }}" id="kelola-prioritas" class="hidden mt-4 border-t border-slate-100 dark:border-white/5 pt-4">
                    @csrf
                    @method('PUT')
                    @if ($errors->any() && $errors->has('subject_ids'))
                        <p class="text-xs text-red-600 dark:text-red-400 mb-2">{{ $errors->first('subject_ids') }}</p>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-48 overflow-y-auto mb-3">
                        @foreach ($allSubjects as $subject)
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                    {{ $prioritySubjects->contains('id', $subject->id) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-primary focus:ring-primary">
                                <span class="ml-2 text-sm text-slate-600 dark:text-white/50">
                                    {{ $subject->name }}
                                    @if ($subject->isQuran())
                                        <span class="text-xs text-purple-600 dark:text-purple-400">(Quran)</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit"
                        class="rounded-lg bg-gradient-to-r from-primary to-secondary px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                        Simpan
                    </button>
                </form>
            </div>
        </div>

        <div data-task="tugas" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 task-card"
            @unless ($showTugas) hidden @endunless>
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Tugas Khusus / PJ</h2>
            </div>
            <div class="px-6 py-4">
                @forelse ($guru->teacherRoles as $role)
                    <div class="flex items-center justify-between mb-2 last:mb-0">
                        <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-700 dark:text-purple-400">
                            {{ $role->role_name }}
                            @if ($role->is_student_related)
                                <span class="ml-1 text-xs text-slate-400 dark:text-white/30">(menangani murid)</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route($routeGroup . '.tugas.destroy', [$guru, $role]) }}"
                            onsubmit="return confirm('Hapus tugas khusus ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 dark:text-white/30">Tidak ada tugas khusus.</p>
                @endforelse

                <form method="POST" action="{{ route($routeGroup . '.tugas.store', $guru) }}" class="mt-4 border-t border-slate-100 dark:border-white/5 pt-4">
                    @csrf
                    @error('role_name')
                        <p class="text-xs text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
                    @enderror
                    <div class="flex flex-wrap items-end gap-2">
                        <div class="flex-1 min-w-[140px]">
                            <label for="role_name" class="block text-xs text-slate-500 dark:text-white/40 mb-1">Nama tugas</label>
                            <input id="role_name" name="role_name" type="text" value="{{ old('role_name') }}"
                                placeholder="Mis. PJ Pramuka"
                                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                        <label class="inline-flex items-center pb-2.5">
                            <input type="checkbox" name="is_student_related" value="1"
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Menangani murid</span>
                        </label>
                        <button type="submit"
                            class="rounded-lg bg-gradient-to-r from-primary to-secondary px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div data-task="tahfidz" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 task-card"
            @unless ($showTahfidz) hidden @endunless>
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Penugasan Tahfidz</h2>
                <span class="text-xs font-medium text-slate-400 dark:text-white/30">{{ $quranAssignmentCount }} murid</span>
            </div>
            <div class="px-6 py-4">
                @forelse ($quranAssignments as $assignment)
                    <div class="flex items-center justify-between gap-3 py-1.5 border-b border-slate-50 dark:border-white/5 last:border-0">
                        <span class="flex items-center gap-3 text-sm text-slate-700 dark:text-white/70">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-500/10 text-xs font-bold text-purple-700 dark:text-purple-400">
                                {{ strtoupper(mb_substr($assignment->student?->name ?? '?', 0, 1)) }}
                            </span>
                            {{ $assignment->student?->name ?? 'Murid tidak ditemukan' }}
                        </span>
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-xs font-medium text-slate-500 dark:text-white/40">
                            {{ $assignment->student?->classRoom?->class_name ?? 'Tanpa kelas' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 dark:text-white/30">Belum ada murid yang dibina.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleKelola(id) {
    var el = document.getElementById(id);
    var btn = document.querySelector('[data-ctl="' + id + '"]');
    if (!el) return;
    var hidden = el.classList.toggle('hidden');
    if (btn) btn.textContent = hidden ? 'Kelola' : 'Batal';
}
function togglePlotClasses(master) {
    document.querySelectorAll('.plot-class').forEach(function (cb) {
        cb.checked = master.checked;
    });
}
function togglePlotAll(master) {
    document.querySelectorAll('.plot-del').forEach(function (b) { b.checked = master.checked; });
    updatePlotSelection();
}
function updatePlotSelection() {
    var boxes = document.querySelectorAll('.plot-del');
    var btn = document.getElementById('plot-batch-btn');
    if (!btn) return;
    var n = 0;
    boxes.forEach(function (b) { if (b.checked) n++; });
    btn.textContent = 'Hapus terpilih (' + n + ')';
    btn.disabled = n === 0;
}
document.querySelectorAll('.plot-del').forEach(function (b) {
    b.addEventListener('change', updatePlotSelection);
});
function revealMissingTasks(btn) {
    document.querySelectorAll('.task-card').forEach(function (card) { card.hidden = false; });
    var stack = document.querySelector('[data-task="stack"]');
    if (stack) stack.hidden = false;
    relayoutTaskGrid();
    if (btn) btn.hidden = true;
}
function relayoutTaskGrid() {
    var plotting = document.querySelector('[data-task="plotting"]');
    var stack = document.querySelector('[data-task="stack"]');
    if (!plotting || !stack) return;
    var plottingHidden = plotting.hasAttribute('hidden');
    var stackEmpty = stack.hasAttribute('hidden') || stack.querySelectorAll('.task-card:not([hidden])').length === 0;
    plotting.classList.toggle('lg:col-span-2', !plottingHidden && stackEmpty);
    stack.classList.toggle('lg:col-span-2', plottingHidden);
}
relayoutTaskGrid();
</script>
@endpush