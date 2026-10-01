@extends('layouts.app')

@section('title', 'Rekap Tahfidz — ' . ($selectedClass->class_name ?? 'Kelas'))

@section('content')
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Rekap Tahfidz — {{ $selectedClass->class_name ?? '-' }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Progress hafalan Al-Quran siswa {{ $selectedClass->class_name ?? '' }}.</p>
        </div>
        @if ($classes->count() > 1)
        <select
            onchange="window.location = this.value"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            @foreach ($classes as $class)
            <option value="{{ route('guru.tahfidz-kelas.index', ['class_id' => $class->id]) }}" @selected($selectedClass && $selectedClass->id === $class->id)>{{ $class->class_name }}</option>
            @endforeach
        </select>
        @endif
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Progress Siswa — {{ $selectedClass->class_name }} ({{ $selectedClass->students_count }} siswa)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ziadah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Murajaah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ayat</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Surah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Skor</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Predikat</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Target</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($studentStats as $sid => $ss)
                @php $target = $studentTargets[$sid] ?? null; @endphp
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 {{ $ss['has_progress'] ? '' : 'bg-white/40 dark:bg-white/[0.02]' }}">
                    <td class="px-4 py-2.5">
                        <a href="{{ route('guru.tahfidz-kelas.student', $sid) }}" class="font-medium text-primary dark:text-primary hover:underline">{{ $ss['name'] }}</a>
                        @if (!$ss['has_progress'])
                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-400 ml-2">Belum ada setoran</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center font-bold">{{ $ss['has_progress'] ? $ss['total'] : '-' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($ss['has_progress'])
                            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $ss['ziadah'] }}</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($ss['has_progress'])
                            <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">{{ $ss['murajaah'] }}</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center">{{ $ss['has_progress'] ? $ss['total_ayat'] : '-' }}</td>
                    <td class="px-4 py-2.5 text-center">{{ $ss['has_progress'] ? $ss['surahs'] : '-' }}</td>
                    <td class="px-4 py-2.5 text-center font-medium">{{ $ss['has_progress'] ? ($ss['avg_score'] ?? '-') : '-' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($ss['has_progress'] && isset($ss['avg_score']))
                            @php $p = \App\Models\TahfidzRecord::scoreToPredikat((int)$ss['avg_score']); @endphp
                            <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-xs font-bold">{{ $p }}</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($target)
                            <div class="w-36">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] text-slate-500 dark:text-white/40 truncate max-w-[100px]">{{ $target['title'] }}</span>
                                    <span class="text-[10px] font-semibold {{ $target['status'] === 'completed' ? 'text-primary dark:text-primary' : 'text-slate-600 dark:text-white/60' }}">{{ $target['percent'] }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
                                    <div class="h-full rounded-full {{ $target['status'] === 'completed' ? 'bg-primary/100' : ($target['status'] === 'overdue' ? 'bg-red-500' : 'bg-primary/100/80') }}" style="width: {{ min(100, $target['percent']) }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 dark:text-white/30">{{ $target['completed_ayat'] }}/{{ $target['total_ayat'] }} ayat</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 dark:text-white/30">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-400 dark:text-white/30">{{ $ss['latest_date'] ? \Carbon\Carbon::parse($ss['latest_date'])->format('d M Y') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada data hafalan untuk kelas ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection