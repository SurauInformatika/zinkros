@extends('layouts.app')

@section('title', 'Nilai ' . ($child?->name ?? 'Anak'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Nilai {{ $child->name }}</h1>
        <p class="text-sm text-slate-500 dark:text-white/50 mt-1">{{ $child->classRoom?->class_name ?? '-' }} · NIS {{ $child->nis }}</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        @include('orangtua._child-switcher')
        <form method="GET" action="{{ route('ortu.nilai') }}" class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-slate-500 dark:text-white/50 mb-1">Tahun Ajaran</label>
            <select name="academic_year_id" onchange="this.form.submit()"
                class="w-full sm:w-56 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" {{ $selectedYear == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if ($grades->isEmpty())
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <p class="text-slate-500 dark:text-white/50">Belum ada data nilai untuk tahun ajaran ini.</p>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach ($bySubject as $group)
            <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-white/5">
                    <div>
                        <h3 class="font-semibold">{{ $group['subject']->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-white/50">{{ $group['records']->count() }} penilaian</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 px-3 py-1 text-sm font-bold text-primary dark:text-primary">
                            {{ $group['average'] }}
                        </span>
                        <p class="text-xs text-slate-400 mt-1">Predikat {{ $group['predikat'] }}</p>
                    </div>
                </div>
<div class="overflow-x-auto">                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-white/50">
                        <tr>
                            <th class="text-left px-5 py-2 font-semibold">Tanggal</th>
                            <th class="text-left px-5 py-2 font-semibold">Tipe</th>
                            <th class="text-right px-5 py-2 font-semibold">Nilai</th>
                            <th class="text-left px-5 py-2 font-semibold">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($group['records'] as $g)
                            <tr>
                                <td class="px-5 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($g->date)->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-2">{{ $g->gradeType?->name ?? '-' }}</td>
                                <td class="px-5 py-2 text-right font-semibold">{{ $g->score }}</td>
                                <td class="px-5 py-2">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-white/10 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-white/70">
                                        {{ \App\Models\TahfidzRecord::scoreToPredikat($g->score) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            </div>
        @endforeach
    </div>
@endif
@endsection
