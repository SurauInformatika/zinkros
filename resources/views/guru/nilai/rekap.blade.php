@extends('layouts.app')

@section('title', 'Rekap Nilai')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Rekap Nilai</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Ringkasan nilai siswa per mata pelajaran dalam bentuk grid.</p>
</div>

<form method="GET" class="mb-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kelas</label>
            <select name="class_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                @foreach ($plottedClasses as $item)
                <option value="{{ $item['class']->id }}" {{ request('class_id', $classRoom?->id) == $item['class']->id ? 'selected' : '' }}>{{ $item['class']->class_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                @foreach ($plottedClasses as $item)
                    @foreach ($item['subjects'] as $s)
                    <option value="{{ $s->id }}" {{ request('subject_id', $subject?->id) == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $item['class']->class_name }})</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tipe</label>
            <select name="grade_type_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                <option value="">Semua Tipe</option>
                @foreach ($gradeTypes as $gt)
                <option value="{{ $gt->id }}" {{ request('grade_type_id', $gradeTypeId) == $gt->id ? 'selected' : '' }}>{{ $gt->name }} ({{ $gt->weight }}%)</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">Tampilkan</button>
        </div>
    </div>
</form>

@if ($subject)
<form method="POST" action="{{ route('guru.nilai.update-kkm') }}" class="mb-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
    @csrf
    @method('PATCH')
    <div class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">KKM {{ $subject->name }}</label>
            <input type="number" name="kkm" min="1" max="100" value="{{ $subject->kkm ?? 80 }}"
                class="w-24 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">Simpan KKM</button>
        <p class="text-xs text-slate-400 dark:text-white/30 pb-1">Nilai remedial di-cap maksimal KKM. Berlaku untuk mata pelajaran {{ $subject->name }} di sekolah ini.</p>
    </div>
</form>
@endif

@if ($classRoom && $subject && $students->isNotEmpty())
@if ($stats)
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Rata-rata</span>
            <span class="text-xs font-medium text-primary dark:text-primary">{{ $studentStats->count() }}/{{ $students->count() }} siswa dinilai</span>
        </div>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['avg'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-primary/100" style="width: {{ $stats['avg'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Tertinggi</span>
            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Max</span>
        </div>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['max'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-blue-500" style="width: {{ $stats['max'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Nilai ≥ 80</span>
            <span class="text-xs font-medium text-primary dark:text-primary">{{ $stats['pct_above80'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['above80'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-primary/100" style="width: {{ $stats['pct_above80'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Nilai < 60</span>
            <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ $stats['pct_below60'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['below60'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-red-500" style="width: {{ $stats['pct_below60'] }}%"></div>
        </div>
    </div>
</div>

@if ($stats['by_type'] && $stats['by_type']->isNotEmpty())
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach ($stats['by_type'] as $typeData)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs text-slate-500 dark:text-white/40">{{ $typeData['name'] }}</span>
            <span class="text-[10px] font-medium text-slate-400 dark:text-white/30">Bobot {{ $typeData['weight'] }}%</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-lg font-bold text-slate-900 dark:text-white">avg {{ $typeData['avg'] }}</span>
            <span class="text-xs text-slate-400 dark:text-white/30">{{ $typeData['count'] }} input</span>
        </div>
    </div>
    @endforeach
</div>
@endif
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">{{ $classRoom->class_name }} &middot; {{ $subject->name }} &middot; {{ $studentStats->count() }}/{{ $students->count() }} siswa dinilai</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 sticky left-0 bg-inherit z-10 min-w-[150px]">Nama Siswa</th>
                    @foreach ($dates as $date)
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40 min-w-[80px]">
                        {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                        <br><span class="text-[10px] font-normal text-slate-400 dark:text-white/30">{{ \Carbon\Carbon::parse($date)->isoFormat('ddd') }}</span>
                    </th>
                    @endforeach
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40 min-w-[50px]">Avg</th>
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40 min-w-[50px]">Min</th>
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40 min-w-[50px]">Max</th>
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40 min-w-[50px]">Jml</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                @php $sStats = $studentStats->get($student->id); @endphp
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-2.5 font-medium sticky left-0 bg-inherit z-10">
                        <a href="{{ route('guru.nilai.student', $student->id) }}" class="hover:text-primary dark:hover:text-primary hover:underline transition-colors">{{ $student->name }}</a>
                    </td>
@foreach ($dates as $date)
                        @php
                            $grade = $allGrades->first(fn($g) => $g->student_id === $student->id && $g->date === $date);
                        @endphp
                        <td class="px-3 py-2.5 text-center">
                            @if ($grade && $grade->finalScore() !== null)
                                <span class="inline-flex items-center justify-center w-10 h-8 rounded-lg {{ $grade->finalScore() >= 80 ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : ($grade->finalScore() >= 60 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400') }} text-xs font-bold"
                                    title="{{ $grade->hasRemedial() ? $grade->score . ' → ' . $grade->remedial_capped . ' (remedial)' : $grade->gradeType?->name }}">{{ $grade->finalScore() }}</span>
                                @if ($grade->hasRemedial())
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 text-[9px] font-bold -ml-1 align-top" title="Remedial, di-cap di KKM">R</span>
                                @endif
                            @else
                                <span class="inline-flex items-center justify-center w-10 h-8 rounded-lg bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-white/15 text-xs">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-3 py-2.5 text-center">
                        @if ($sStats)
                        <span class="inline-flex items-center rounded-md {{ $sStats['avg'] >= 80 ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : ($sStats['avg'] >= 60 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400') }} px-2 py-0.5 text-xs font-bold">{{ $sStats['avg'] }}</span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-white/15">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-center text-xs font-medium text-red-600 dark:text-red-400">{{ $sStats['min'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-center text-xs font-medium text-primary dark:text-primary">{{ $sStats['max'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-center text-xs font-medium text-slate-500 dark:text-white/40">{{ $sStats['count'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-100 dark:border-white/5 flex items-center gap-4 text-xs text-slate-500 dark:text-white/40">
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary font-bold text-[10px]">80+</span> Baik</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-[10px]">60+</span> Cukup</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold text-[10px]">&lt;60</span> Kurang</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-white/15 font-bold text-[10px]">-</span> Belum ada</span>
        <span><span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 font-bold text-[9px]">R</span> Remedial (nilai akhir, di-cap di KKM)</span>
    </div>
</div>
@elseif ($classRoom)
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-white/10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <p class="text-slate-500 dark:text-white/40 font-medium">Belum ada data nilai</p>
</div>
@endif
@endsection
