@extends('layouts.app')

@section('title', 'Dashboard Orang Tua')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Dashboard Orang Tua</h1>
        <p class="text-sm text-slate-500 dark:text-white/50 mt-1">Pantau perkembangan putra/putri Anda</p>
    </div>
    @include('orangtua._child-switcher')
</div>

@if ($children->isEmpty())
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <p class="text-slate-500 dark:text-white/50">Tidak ada anak yang terhubung ke akun Anda.</p>
        <p class="text-sm text-slate-400 mt-2">Silakan hubungi pihak sekolah untuk menghubungkan akun Anda dengan data anak.</p>
    </div>
@elseif (!$child)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <p class="text-slate-500 dark:text-white/50">Pilih anak untuk melihat informasi.</p>
    </div>
@else
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-primary to-secondary p-6 text-white shadow-lg shadow-primary/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-white/80 text-sm">Aktif melihat</p>
                <h2 class="text-2xl font-bold mt-0.5">{{ $child->name }}</h2>
                <p class="text-white/80 text-sm mt-1">
                    {{ $child->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} ·
                    {{ $child->classRoom?->class_name ?? '-' }} ·
                    NIS {{ $child->nis }}
                </p>
            </div>
            <div class="hidden sm:flex items-center justify-center w-16 h-16 rounded-full bg-white/20">
                <span class="text-2xl font-bold">{{ mb_substr($child->name, 0, 1) }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @include('orangtua._stat-card', [
            'label' => 'Rata-rata Nilai',
            'value' => $stats['grades']['average'] ?? '—',
            'color' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
            'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        ])
        @include('orangtua._stat-card', [
            'label' => 'Kehadiran',
            'value' => $stats['attendance']['hadir'] ?? 0,
            'suffix' => '/ ' . ($stats['attendance']['total'] ?? 0),
            'color' => 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ])
        @include('orangtua._stat-card', [
            'label' => 'Ayat Terhafal',
            'value' => $stats['tahfidz']['total_ayat'] ?? 0,
            'suffix' => 'ayat',
            'color' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400',
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        ])
        @include('orangtua._stat-card', [
            'label' => 'Hafalan Diinput',
            'value' => ($stats['tahfidz']['ziadah'] + $stats['tahfidz']['murajaah']) ?? 0,
            'suffix' => 'catatan',
            'color' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ])
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold">Kehadiran Mapel</h3>
                <a href="{{ route('ortu.absensi') }}" class="text-sm text-primary dark:text-primary hover:underline">Detail</a>
            </div>
            @php $att = $stats['attendance']; @endphp
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 px-3 py-1 text-sm font-medium text-primary dark:text-primary">H {{ $att['hadir'] }}</span>
                <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-400">I {{ $att['izin'] }}</span>
                <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-3 py-1 text-sm font-medium text-amber-700 dark:text-amber-400">S {{ $att['sakit'] }}</span>
                <span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-500/10 px-3 py-1 text-sm font-medium text-red-700 dark:text-red-400">A {{ $att['alpa'] }}</span>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold">Hafalan Al-Quran</h3>
                <a href="{{ route('ortu.hafalan') }}" class="text-sm text-primary dark:text-primary hover:underline">Detail</a>
            </div>
            @php $th = $stats['tahfidz']; @endphp
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-purple-50 dark:bg-purple-500/10 px-3 py-1 text-sm font-medium text-purple-700 dark:text-purple-400">Ziadah {{ $th['ziadah'] }}</span>
                <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 text-sm font-medium text-indigo-700 dark:text-indigo-400">Murajaah {{ $th['murajaah'] }}</span>
                @if ($th['avg_score'])
                    <span class="inline-flex items-center rounded-full bg-secondary/10 dark:bg-secondary/10 px-3 py-1 text-sm font-medium text-secondary dark:text-secondary">Rata-rata {{ $th['avg_score'] }}</span>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
