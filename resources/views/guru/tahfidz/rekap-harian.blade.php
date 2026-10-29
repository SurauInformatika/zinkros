@extends('layouts.app')

@section('title', 'Rekap Setoran Harian')

@section('content')
@php
    $filters = [
        'hari_ini' => 'Hari Ini',
        'pekan_ini' => 'Pekan Ini',
        'bulan_ini' => 'Bulan Ini',
        'semester_ini' => 'Semester Ini',
        'tahun_ajaran_ini' => 'Tahun Ajaran Ini',
    ];
@endphp

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Rekap Setoran Harian</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $classRoom->class_name }}</p>
    </div>
    <a href="{{ route('guru.tahfidz.rekap', ['class_id' => $classRoom->id]) }}"
        class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium transition hover:bg-slate-50 dark:border-white/10 dark:hover:bg-white/5">
        Kembali ke Rekap
    </a>
</div>

<nav aria-label="Filter periode" class="mb-6 flex flex-wrap gap-2">
    @foreach ($filters as $key => $label)
    <a href="{{ route('guru.tahfidz.rekap-harian', ['class_id' => $classRoom->id, 'filter' => $key]) }}"
        @class([
            'rounded-lg border px-3 py-2 text-sm font-medium transition',
            'border-primary bg-primary text-white' => $filter === $key,
            'border-slate-200 bg-white text-slate-600 hover:border-primary/40 dark:border-white/10 dark:bg-[#141414] dark:text-white/60' => $filter !== $key,
        ])>
        {{ $label }}
    </a>
    @endforeach
</nav>

<p class="mb-4 text-sm text-slate-500 dark:text-white/40">
    Periode {{ \Carbon\Carbon::parse($rangeStart)->translatedFormat('d M Y') }}
    – {{ \Carbon\Carbon::parse($rangeEnd)->translatedFormat('d M Y') }}.
    Hari tanpa catatan setoran tidak ditampilkan.
</p>

@if ($dateCards->isNotEmpty())
<div class="space-y-3">
    @foreach ($dateCards as $card)
    <article class="rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-[#141414]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold">{{ \Carbon\Carbon::parse($card['date'])->translatedFormat('l, d M Y') }}</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-white/40">{{ $card['total'] }} siswa di kelas</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">Hadir {{ $card['hadir'] }}</span>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Tidak hadir {{ $card['tidak_hadir'] }}</span>
                <a href="{{ route('guru.tahfidz.rekap-harian.detail', ['date' => $card['date'], 'class_id' => $classRoom->id, 'filter' => $filter]) }}"
                    aria-label="Lihat detail setoran {{ \Carbon\Carbon::parse($card['date'])->translatedFormat('d M Y') }}"
                    title="Lihat detail"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white transition hover:opacity-90">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </a>
            </div>
        </div>
    </article>
    @endforeach
</div>
@else
<div class="rounded-xl border border-slate-200 bg-white p-8 text-center dark:border-white/10 dark:bg-[#141414]">
    <p class="text-sm text-slate-500 dark:text-white/40">Belum ada data setoran pada periode ini.</p>
</div>
@endif

@endsection
