@extends('layouts.app')

@section('title', 'Absensi Mapel ' . ($child?->name ?? 'Anak'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('ortu.absensi') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary mb-1 inline-block">&larr; Semua Absensi</a>
        <h1 class="text-2xl font-bold">Absensi Mapel {{ $child->name }}</h1>
        <p class="text-sm text-slate-500 dark:text-white/50 mt-1">{{ $child->classRoom?->class_name ?? '-' }} · NIS {{ $child->nis }}</p>
    </div>
    @include('orangtua._child-switcher')
</div>

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <a href="{{ route('ortu.absensi.kelas', ['period' => $period]) }}" class="text-sm text-primary dark:text-primary hover:underline">Lihat Absensi Kelas &rarr;</a>

    <div class="flex flex-wrap items-center gap-1.5">
        @php
            $periods = [
                'pekan' => 'Pekan Ini',
                'bulan' => 'Bulan Ini',
                'semester' => 'Semester Ini',
                'ta' => 'Tahun Ajaran Ini',
            ];
        @endphp
        @foreach ($periods as $key => $label)
            <a href="{{ route('ortu.absensi.mapel', ['period' => $key]) }}"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition
                    {{ $period === $key
                        ? 'bg-primary text-white border-primary'
                        : 'bg-white dark:bg-[#141414] text-slate-600 dark:text-white/60 border-slate-200 dark:border-white/10 hover:border-primary/30' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

@if ($from && $to)
    <p class="text-xs text-slate-500 dark:text-white/50 mb-4">
        Periode: {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
    </p>
@endif

@php
    $statusColors = [
        'HADIR' => 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary',
        'IZIN' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
        'SAKIT' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
        'ALPA' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
    ];
    $summaryItems = [
        ['key' => 'total', 'label' => 'Total', 'color' => 'text-slate-700 dark:text-white'],
        ['key' => 'hadir', 'label' => 'Hadir', 'color' => 'text-primary dark:text-primary'],
        ['key' => 'izin', 'label' => 'Izin', 'color' => 'text-blue-600 dark:text-blue-400'],
        ['key' => 'sakit', 'label' => 'Sakit', 'color' => 'text-amber-600 dark:text-amber-400'],
        ['key' => 'alpa', 'label' => 'Alpa', 'color' => 'text-red-600 dark:text-red-400'],
    ];
@endphp

<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
    @foreach ($summaryItems as $it)
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold {{ $it['color'] }}">{{ $summary[$it['key']] }}</p>
            <p class="text-xs text-slate-500 dark:text-white/50 mt-1">{{ $it['label'] }}</p>
        </div>
    @endforeach
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    @if ($records->isEmpty())
        <p class="p-6 text-sm text-slate-500 dark:text-white/50">Belum ada data absensi mapel pada periode ini.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-white/50">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Tanggal</th>
                        <th class="text-left px-5 py-3 font-semibold">Mata Pelajaran</th>
                        <th class="text-left px-5 py-3 font-semibold">Guru</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="hidden sm:table-cell text-left px-5 py-3 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($records as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                            <td class="px-5 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($record->date)->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3">{{ $record->subject?->name ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $record->teacher?->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full {{ $statusColors[$record->status] ?? 'bg-slate-100 text-slate-600' }} px-2.5 py-0.5 text-xs font-medium">{{ $record->status }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-3 text-slate-500 dark:text-white/50">{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
