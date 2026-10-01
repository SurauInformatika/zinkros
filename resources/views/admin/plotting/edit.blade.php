@extends('layouts.app')

@section('title', 'Plotting: ' . $class->class_name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.plotting.index') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar kelas</a>
    <h1 class="mt-2 text-2xl font-bold tracking-tight">Plotting Mapel — {{ $class->class_name }}</h1>
    @if ($class->walis->isNotEmpty())
        <p class="text-sm text-slate-500 dark:text-white/40">Wali Kelas: {{ $class->waliNames() }}</p>
    @endif
</div>

@if ($errors->any())
    <div class="mb-4 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/10 dark:to-rose-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $general = $subjects->where('type', \App\Models\Subject::TYPE_GENERAL);
    $quran = $subjects->where('type', \App\Models\Subject::TYPE_QURAN);
@endphp

<form method="POST" action="{{ route('admin.plotting.update', $class) }}" id="plottingForm">
    @csrf
    @method('PUT')

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" id="teacherSearch" placeholder="Cari guru..." autocomplete="off"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] pl-10 pr-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none">
        </div>
    </div>

    @if ($general->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white/80 mb-3">Mapel Umum</h2>
            <div class="space-y-4">
                @foreach ($general as $subject)
                    @php
                        $selected = $current[$subject->id] ?? [];
                        $count = count($selected);
                    @endphp
                    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] overflow-hidden subject-card" data-subject-id="{{ $subject->id }}">
                        <button type="button" onclick="toggleCard(this)" class="flex w-full items-center justify-between px-4 py-3 text-left">
                            <div class="flex items-center gap-3">
                                <svg class="h-4 w-4 text-slate-400 transition-transform chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white">{{ $subject->name }}</h3>
                                <span class="count-badge rounded-full bg-primary/15 dark:bg-primary/100/20 px-2 py-0.5 text-xs font-semibold text-primary dark:text-primary">{{ $count }}</span>
                            </div>
                        </button>
                        <div class="teacher-list hidden border-t border-slate-100 dark:border-white/5 p-3 space-y-1">
                            @forelse ($teachers as $teacher)
                                @php $plots = $existingPlots[$teacher->id] ?? []; @endphp
                                <label class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer teacher-row"
                                    data-name="{{ strtolower($teacher->name) }}">
                                    <input type="checkbox" name="assignments[{{ $subject->id }}][]"
                                        value="{{ $teacher->id }}"
                                        {{ in_array($teacher->id, $selected) ? 'checked' : '' }}
                                        onchange="updateCount('{{ $subject->id }}')"
                                        class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $teacher->name }}</p>
                                    </div>
                                    @if ($plots)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($plots as $plot)
                                                <span class="rounded bg-slate-100 dark:bg-white/10 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 dark:text-white/40">{{ $plot }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </label>
                            @empty
                                <p class="px-3 py-4 text-center text-sm text-slate-400">Tidak ada guru.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($quran->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white/80 mb-3">Mapel Quran (Halqah)</h2>
            <div class="space-y-4">
                @foreach ($quran as $subject)
                    @php
                        $selected = $current[$subject->id] ?? [];
                        $count = count($selected);
                    @endphp
                    <div class="rounded-xl border border-purple-200 dark:border-purple-500/20 bg-white dark:bg-[#141414] overflow-hidden subject-card" data-subject-id="{{ $subject->id }}">
                        <button type="button" onclick="toggleCard(this)" class="flex w-full items-center justify-between px-4 py-3 text-left">
                            <div class="flex items-center gap-3">
                                <svg class="h-4 w-4 text-slate-400 transition-transform chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                <h3 class="text-sm font-bold text-purple-700 dark:text-purple-400">{{ $subject->name }}</h3>
                                <span class="count-badge rounded-full bg-purple-100 dark:bg-purple-500/20 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:text-purple-400">{{ $count }}</span>
                            </div>
                        </button>
                        <div class="teacher-list hidden border-t border-purple-100 dark:border-purple-500/10 p-3 space-y-1">
                            @forelse ($teachers as $teacher)
                                @php $plots = $existingPlots[$teacher->id] ?? []; @endphp
                                <label class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition hover:bg-purple-50 dark:hover:bg-purple-500/5 cursor-pointer teacher-row"
                                    data-name="{{ strtolower($teacher->name) }}">
                                    <input type="checkbox" name="assignments[{{ $subject->id }}][]"
                                        value="{{ $teacher->id }}"
                                        {{ in_array($teacher->id, $selected) ? 'checked' : '' }}
                                        onchange="updateCount('{{ $subject->id }}')"
                                        class="h-4 w-4 rounded border-purple-300 text-purple-600 focus:ring-purple-500">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $teacher->name }}</p>
                                    </div>
                                    @if ($plots)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($plots as $plot)
                                                <span class="rounded bg-purple-100 dark:bg-purple-500/10 px-1.5 py-0.5 text-[10px] font-medium text-purple-500 dark:text-purple-300/60">{{ $plot }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </label>
                            @empty
                                <p class="px-3 py-4 text-center text-sm text-slate-400">Tidak ada guru.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($subjects->isEmpty())
        <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-5 py-4 text-sm text-amber-700 dark:text-amber-400">
            Belum ada mata pelajaran. Buat terlebih dulu di menu <a href="{{ route('admin.subject.index') }}" class="font-semibold underline">Manajemen Mapel</a>.
        </div>
    @endif

    <div class="flex items-center gap-3 pt-2 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan Plotting
        </button>
        <a href="{{ route('admin.plotting.index') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800 dark:hover:text-white">Batal</a>
    </div>
</form>

<script>
    // Search teacher
    document.getElementById('teacherSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.teacher-row').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    });

    // Update count badge
    function updateCount(subjectId) {
        const card = document.querySelector('[data-subject-id="' + subjectId + '"]');
        if (!card) return;
        const checked = card.querySelectorAll('input[type="checkbox"]:checked').length;
        const badge = card.querySelector('.count-badge');
        if (badge) badge.textContent = checked;
    }

    // Toggle card expand/collapse
    function toggleCard(btn) {
        const card = btn.closest('.subject-card');
        const list = card.querySelector('.teacher-list');
        const chevron = btn.querySelector('.chevron');
        list.classList.toggle('hidden');
        chevron.style.transform = list.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    // Init counts + auto-expand cards with selection
    document.querySelectorAll('.subject-card').forEach(card => {
        updateCount(card.dataset.subjectId);
        const checked = card.querySelectorAll('input[type="checkbox"]:checked').length;
        if (checked > 0) {
            const list = card.querySelector('.teacher-list');
            const chevron = card.querySelector('.chevron');
            list.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    });
</script>
@endsection
