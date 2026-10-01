@extends('layouts.app')

@section('title', 'Roster / Jadwal Kelas')

@section('content')
<div class="mb-6 no-print">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Roster / Jadwal Kelas</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Pembagian mapel dan jadwal mingguan per kelas.</p>
        </div>
        @if ($mode !== 'guru')
            <button type="button" onclick="printRosterAll()"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-2 text-sm font-medium hover:opacity-90 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Semua
            </button>
        @endif
    </div>
</div>

@php
    $queryKeep = collect(['ta' => $ta, 'q' => $q !== '' ? $q : null])->filter(fn ($v) => $v !== null)->all();
@endphp

<div class="mb-6 no-print flex flex-wrap items-center gap-2">
    <div class="inline-flex rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] p-1">
        @foreach ([
            'mapel' => 'Pembagian Mapel',
            'jadwal' => 'Jadwal Mingguan',
            'guru' => 'Jadwal Guru',
        ] as $key => $label)
            <a href="{{ route('wakasek.base.roster', ['view' => $key] + $queryKeep) }}"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition
                    {{ $mode === $key ? 'bg-primary text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($mode === 'guru')
        <form method="GET" action="{{ route('wakasek.base.roster') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="view" value="guru">
            @if ($ta !== null)
                <input type="hidden" name="ta" value="{{ $ta }}">
            @endif
            <select name="guru" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-3 py-2 text-sm">
                <option value="" @selected($selectedGuru === null)>Pilih guru...</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected($selectedGuru === $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

<div class="mb-6 no-print">
    <form method="GET" action="{{ route('wakasek.base.roster') }}" class="grid gap-3 sm:grid-cols-2 lg:max-w-2xl">
        @if ($mode !== 'mapel')
            <input type="hidden" name="view" value="{{ $mode }}">
        @endif
        @if ($mode === 'guru')
            <input type="hidden" name="guru" value="{{ $selectedGuru ?? '' }}">
        @endif
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 dark:text-white/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari kelas..."
                class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] pl-10 pr-12 py-2.5 text-sm placeholder-slate-400 dark:placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-primary/40">
            @if ($q !== '')
                <a href="{{ route('wakasek.base.roster', ['view' => $mode] + $queryKeep) }}" class="absolute inset-y-0 right-3 flex items-center text-slate-400 dark:text-white/30 hover:text-slate-600 dark:hover:text-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </div>
        <div>
            <select name="ta" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
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

@php $days = App\Models\ClassDaySchedule::DAYS; @endphp
@if ($mode === 'jadwal')
    @include('wakakur.base.roster.partials.jadwal')
@elseif ($mode === 'guru')
    @include('wakakur.base.roster.partials.guru')
@else
    @include('wakakur.base.roster.partials.mapel')
@endif

<style>
    @media print {
        body { background: #fff !important; }
        #sidebar-desktop, #sidebar, #sidebar-backdrop, header, .no-print { display: none !important; }
        #main-content { margin-left: 0 !important; }
        main { padding: 0 !important; }
        body.roster-print-all .roster-card { display: block !important; break-inside: avoid; }
        body.roster-print-card .roster-card { display: none !important; }
        body.roster-print-card .roster-card.roster-print-active { display: block !important; break-inside: avoid; }
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
    function printRosterAll() {
        document.body.classList.add('roster-print-all');
        window.print();
        document.body.classList.remove('roster-print-all');
    }
</script>
@endsection