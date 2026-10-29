@extends('layouts.app')

@section('title', 'Overview Tahfidz')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Overview Tahfidz</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Pantau progress hafalan Al-Quran siswa per kelas.</p>
</div>

<div class="mb-6 flex flex-wrap items-center gap-3">
    <div class="flex items-center gap-2">
        <label for="class_filter" class="text-sm text-slate-600 dark:text-white/60">Pilih Kelas</label>
        <select id="class_filter" onchange="if(this.value){ window.location.href='{{ route($routeGroup . '.index') }}?class_id='+this.value; } else { window.location.href='{{ route($routeGroup . '.index') }}'; }" class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="" {{ empty($classId) ? 'selected' : '' }}>Semua Kelas</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" {{ ($classId ?? null) === $class->id ? 'selected' : '' }}>{{ $class->class_name }} ({{ $class->students_count }} siswa)</option>
            @endforeach
        </select>
    </div>
    @if ($classId && $selectedClass)
        <span class="text-sm text-slate-500 dark:text-white/40">Menampilkan: {{ $selectedClass->class_name }}</span>
    @endif
</div>

@if ($classId && $selectedClass)
    @if ($stats)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Hafalan</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Ayat</p>
            <p class="text-2xl font-bold">{{ $stats['total_ayat'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Surah Dihafal</p>
            <p class="text-2xl font-bold">{{ $stats['unique_surahs'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Rata-rata Skor</p>
            @php $p = \App\Models\TahfidzRecord::scoreToPredikat((int)($stats['avg_score'] ?? 0)); @endphp
            <p class="text-2xl font-bold">
                {{ $stats['avg_score'] ?? '-' }}
                <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-sm font-bold ml-1">{{ $p }}</span>
            </p>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 dark:border-white/5">
            <h2 class="font-semibold">Progress Siswa — {{ $selectedClass->class_name }}</h2>
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
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5">
                            <a href="{{ route($routeGroup . '.student', $sid) }}" class="font-medium text-primary dark:text-primary hover:underline">{{ $ss['name'] }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-center font-bold">{{ $ss['total'] }}</td>
                        <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $ss['ziadah'] }}</span></td>
                        <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">{{ $ss['murajaah'] }}</span></td>
                        <td class="px-4 py-2.5 text-center">{{ $ss['total_ayat'] }}</td>
                        <td class="px-4 py-2.5 text-center">{{ $ss['surahs'] }}</td>
                        <td class="px-4 py-2.5 text-center font-medium">{{ $ss['avg_score'] ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @php $p = isset($ss['avg_score']) ? \App\Models\TahfidzRecord::scoreToPredikat((int)$ss['avg_score']) : '-'; @endphp
                            @if ($p !== '-')
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
                                        <div class="h-full rounded-full {{ $target['status'] === 'completed' ? 'bg-primary/100' : ($target['status'] === 'overdue' ? 'bg-red-500' : 'bg-primary/80') }}" style="width: {{ min(100, $target['percent']) }}%"></div>
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
    @else
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p class="text-sm text-slate-500 dark:text-white/40">Belum ada data hafalan untuk kelas ini.</p>
    </div>
    @endif
@else
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/5">
            <h2 class="font-semibold">Ringkasan per Kelas</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-white/40">Pilih kelas untuk melihat detail hafalan siswa</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Tingkat</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Jumlah Siswa</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500 dark:text-white/40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $class)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5 font-medium">{{ $class->class_name }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-600 dark:text-white/60">{{ $class->grade_level }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-600 dark:text-white/60">{{ $class->students_count }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route($routeGroup . '.index', ['class_id' => $class->id]) }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                                Lihat Detail
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
