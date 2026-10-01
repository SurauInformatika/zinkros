@extends('layouts.app')

@section('title', 'Rekap Absensi Kelas')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Rekap Absensi Kelas</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Lihat rekap absensi harian per kelas dalam bentuk grid.</p>
</div>

<form method="GET" class="mb-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kelas</label>
            <select name="class_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                @foreach ($waliClasses as $class)
                <option value="{{ $class->id }}" {{ request('class_id', $classRoom?->id) == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
                Tampilkan
            </button>
        </div>
    </div>
</form>

@if ($classRoom && $students->isNotEmpty())
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Hadir</span>
            <span class="text-xs font-medium text-primary dark:text-primary">{{ $stats['persentase_hadir'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['hadir'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-primary/100" style="width: {{ $stats['persentase_hadir'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Sakit</span>
            <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ $stats['persentase_sakit'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['sakit'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-amber-500" style="width: {{ $stats['persentase_sakit'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Izin</span>
            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ $stats['persentase_izin'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['izin'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-blue-500" style="width: {{ $stats['persentase_izin'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Alpa</span>
            <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ $stats['persentase_alpa'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['alpa'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-red-500" style="width: {{ $stats['persentase_alpa'] }}%"></div>
        </div>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">{{ $classRoom->class_name }} &middot; {{ count($dates) }} hari</h2>
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
                    <th class="px-3 py-3 text-center font-medium text-primary dark:text-primary min-w-[50px]">H</th>
                    <th class="px-3 py-3 text-center font-medium text-amber-600 dark:text-amber-400 min-w-[50px]">S</th>
                    <th class="px-3 py-3 text-center font-medium text-blue-600 dark:text-blue-400 min-w-[50px]">I</th>
                    <th class="px-3 py-3 text-center font-medium text-red-600 dark:text-red-400 min-w-[50px]">A</th>
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40 min-w-[60px]">%Hadir</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="px-4 py-2.5 font-medium sticky left-0 bg-inherit z-10">
                        <a href="{{ route('guru.absensi-kelas.student', ['studentId' => $student->id, 'class_id' => $classRoom->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="hover:text-primary dark:hover:text-primary transition-colors">{{ $student->name }}</a>
                    </td>
                    @foreach ($dates as $date)
                        @php $record = $rekapData->get($student->id . '_' . $date); @endphp
                        <td class="px-3 py-2.5 text-center">
                            @if ($record)
                                @if ($record->status === 'HADIR')
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary text-xs font-bold">H</span>
                                @elseif ($record->status === 'SAKIT')
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold" title="{{ $record->notes }}">S</span>
                                @elseif ($record->status === 'IZIN')
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-bold" title="{{ $record->notes }}">I</span>
                                @else
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-bold" title="{{ $record->notes }}">A</span>
                                @endif
                            @else
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-white/15 text-xs">-</span>
                            @endif
                        </td>
                    @endforeach
                    @php $sStats = $studentStats[$student->id] ?? null; @endphp
                    <td class="px-3 py-2.5 text-center text-xs font-bold text-primary dark:text-primary">{{ $sStats['hadir'] ?? 0 }}</td>
                    <td class="px-3 py-2.5 text-center text-xs font-bold text-amber-600 dark:text-amber-400">{{ $sStats['sakit'] ?? 0 }}</td>
                    <td class="px-3 py-2.5 text-center text-xs font-bold text-blue-600 dark:text-blue-400">{{ $sStats['izin'] ?? 0 }}</td>
                    <td class="px-3 py-2.5 text-center text-xs font-bold text-red-600 dark:text-red-400">{{ $sStats['alpa'] ?? 0 }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if ($sStats)
                        <span class="inline-flex items-center rounded-md {{ $sStats['persentase_hadir'] >= 80 ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : ($sStats['persentase_hadir'] >= 50 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400') }} px-2 py-0.5 text-xs font-medium">{{ $sStats['persentase_hadir'] }}%</span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-white/15">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-100 dark:border-white/5 flex items-center gap-4 text-xs text-slate-500 dark:text-white/40">
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary font-bold">H</span> Hadir</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold">S</span> Sakit</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold">I</span> Izin</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold">A</span> Alpa</span>
        <span><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-slate-50 dark:bg-white/5 text-slate-300 dark:text-white/15 font-bold">-</span> Belum diisi</span>
    </div>
</div>
@elseif ($classRoom)
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-white/10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <p class="text-slate-500 dark:text-white/40 font-medium">Belum ada data absensi untuk rentang tanggal ini</p>
</div>
@endif
@endsection
