@extends('layouts.app')

@section('title', 'Roster Kelas')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Roster Kelas</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Pembagian mapel per kelas.</p>
    </div>
    @if ($selectedKelas)
        <button type="button" onclick="printRosterCard('{{ $selectedKelas->id }}')"
            class="no-print inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-2 text-sm font-medium hover:opacity-90 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    @endif
</div>

<div class="mb-6 no-print">
    <form method="GET" action="{{ route('kepsek.roster') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label for="kelas" class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kelas</label>
            <select id="kelas" name="kelas" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected($selectedKelas && $selectedKelas->id === $class->id)>{{ $class->class_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="ta" class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tahun Ajaran</label>
            <select id="ta" name="ta" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($ta === $year->id)>{{ $year->name }}</option>
                @endforeach
                @if ($ta === null)
                    <option value="" selected>Semua Tahun Ajaran</option>
                @endif
            </select>
        </div>
    </form>
</div>

@if (! $selectedKelas)
    <div class="no-print rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center text-slate-400 dark:text-white/30 text-sm">
        Belum ada data kelas.
    </div>
@else
<div id="roster-list" class="space-y-4">
    @php
        $rows = $rosterRows;
        $complete = $rows->filter(fn ($row) => $row->teacher_id)->count();
        $missing = $rows->count() - $complete;
    @endphp
    <div class="roster-card rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5" data-class-card="{{ $selectedKelas->id }}">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div class="min-w-0">
                <h3 class="font-semibold text-lg">{{ $selectedKelas->class_name }}</h3>
                <p class="text-xs text-slate-400 dark:text-white/30 mt-0.5">
                    Wali: <span class="font-medium text-slate-500 dark:text-white/50">{{ $selectedKelas->waliNames() }}</span>
                    <span class="mx-1.5">·</span>
                    <span class="font-medium">{{ $rows->count() }} mapel</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if ($missing > 0)
                    <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                        {{ $missing }} belum ada guru
                    </span>
                @elseif ($rows->count() > 0)
                    <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                        Lengkap
                    </span>
                @endif
            </div>
        </div>

        @if ($rows->count() > 0)
            <div class="space-y-1.5">
                @foreach ($rows as $row)
                    <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 {{ $row->teacher_id ? 'bg-slate-50 dark:bg-white/5' : 'bg-red-50/60 dark:bg-red-500/10' }}">
                        <div class="flex items-center gap-2 min-w-0">
                            @if ($row->subject?->type === 'QURAN')
                                <svg class="w-3.5 h-3.5 shrink-0 text-primary dark:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            @endif
                            <span class="text-sm font-medium text-slate-700 dark:text-white/70">{{ $row->subject?->name ?? 'Tanpa nama' }}</span>
                        </div>
                        @if ($row->teacher_id && $row->teacher)
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $row->teacher->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-red-100 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-2.5 py-1 text-xs font-medium text-red-700 dark:text-red-400">
                                Belum ada guru
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400 dark:text-white/30">Belum ada mapel diampu.</p>
        @endif
    </div>
</div>
@endif

<style>
    @media print {
        body { background: #fff !important; }
        #sidebar-desktop, #sidebar, #sidebar-backdrop, header, .no-print { display: none !important; }
        #main-content { margin-left: 0 !important; }
        main { padding: 0 !important; }
        .roster-card { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection

@section('scripts')
<script>
    function printRosterCard(id) {
        document.querySelectorAll('.roster-card').forEach(function (card) {
            card.classList.toggle('roster-print-active', card.dataset.classCard === String(id));
        });
        document.body.classList.add('roster-print-card');
        window.print();
        document.body.classList.remove('roster-print-card');
    }
</script>
@endsection
