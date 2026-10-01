@extends('layouts.app')

@section('title', 'Nilai — ' . $student->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('guru.nilai.history') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary">&larr; Kembali</a>
</div>

<div class="mb-8">
    <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25">
            <span class="text-lg font-bold text-white">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $student->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-white/40">{{ $student->classRoom?->class_name ?? '-' }}</p>
        </div>
    </div>
</div>

@if ($stats)
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold">{{ $stats['avg'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Rata-rata</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['max'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Tertinggi</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['min'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Terendah</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Total Input</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['above80'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Score ≥ 80</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['below60'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Score &lt; 60</p>
    </div>
</div>
@endif

@if ($bySubject->isEmpty())
<div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-12 text-center">
    <p class="text-sm text-slate-400 dark:text-white/30">Belum ada nilai untuk siswa ini.</p>
</div>
@else
<div class="space-y-6">
    @foreach ($bySubject as $subjectData)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02] px-5 py-4 flex items-center justify-between">
            <h2 class="font-semibold text-slate-700 dark:text-white/80">{{ $subjectData['subject_name'] }}</h2>
            @if ($subjectData['avg'] !== null)
                <span class="inline-flex items-center rounded-md {{ $subjectData['avg'] >= 80 ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : ($subjectData['avg'] >= 60 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400') }} px-2.5 py-1 text-xs font-bold">Rata-rata: {{ $subjectData['avg'] }}</span>
            @endif
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($subjectData['by_type'] as $typeData)
                <div class="rounded-lg border border-slate-100 dark:border-white/5 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $typeData['type_name'] }}</h3>
                        <span class="text-xs text-slate-400 dark:text-white/30">{{ $typeData['weight'] }}%</span>
                    </div>
                    @if ($typeData['records']->isNotEmpty())
                        <div class="mb-3">
                            @php $typeScores = $typeData['records']->pluck('score')->filter(); @endphp
                            @if ($typeScores->isNotEmpty())
                                @php $avg = round($typeScores->avg(), 1); @endphp
                                <span class="inline-flex items-center rounded-md {{ $avg >= 80 ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : ($avg >= 60 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400') }} px-2 py-0.5 text-xs font-bold">{{ $avg }}</span>
                            @endif
                        </div>
                    @endif
                    <div class="space-y-1.5">
                        @foreach ($typeData['records'] as $rec)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-white/40">{{ \Carbon\Carbon::parse($rec['date'])->format('d M') }}</span>
                            <div class="flex items-center gap-2">
                                @if ($rec['score'] !== null)
                                    <span class="font-bold {{ $rec['score'] >= 80 ? 'text-primary dark:text-primary' : ($rec['score'] >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}" title="{{ $rec['is_remedial'] ? $rec['original_score'] . ' → ' . $rec['score'] . ' (remedial)' : '' }}">{{ $rec['score'] }}</span>
                                    @if ($rec['is_remedial'])
                                    <span class="inline-flex items-center justify-center rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 text-[9px] font-bold px-1" title="Remedial, di-cap di KKM">R</span>
                                    @endif
                                @else
                                    <span class="text-slate-300 dark:text-white/15">—</span>
                                @endif
                            </div>
                        </div>
                        @if ($rec['notes'])
                            <div class="text-[10px] text-slate-400 dark:text-white/25 pl-12 -mt-1">{{ $rec['notes'] }}</div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
