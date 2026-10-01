@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Assalamu'alaikum, {{ auth()->user()->name }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
        @if ($isPJtahfidz && $isWaliKelas)
            Panel Guru Tahfidz & Wali Kelas
        @elseif ($isPJtahfidz)
            Panel Guru Tahfidz
        @elseif ($isWaliKelas)
            Panel Wali Kelas
        @else
            Selamat datang di panel guru Anda.
        @endif
    </p>
</div>

@if ($isPJtahfidz && $tahfidzData)

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-slate-500 dark:text-white/40">Siswa Binaan</span>
                <span class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 7.292 4 4 0 010-7.292zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold tracking-tight">{{ $tahfidzData['total_siswa'] }}</p>
        </div>

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-slate-500 dark:text-white/40">Total Setoran</span>
                <span class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-primary/100/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary dark:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold tracking-tight">{{ $tahfidzData['total_setoran'] }}</p>
        </div>

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-slate-500 dark:text-white/40">Rata-rata Skor</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold tracking-tight">{{ $tahfidzData['avg_score'] }}</p>
        </div>
    </div>

    @if ($tahfidzData['students']->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Siswa Binaan</h2>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden print:break-inside-avoid">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                            <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Setoran</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tahfidzData['students'] as $s)
                        <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] break-inside-avoid print:break-inside-avoid">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('guru.tahfidz.student', $s['student']->id) }}" class="text-primary dark:text-primary hover:underline">{{ $s['student']->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $s['student']->classRoom?->class_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $s['total_setoran'] }}x</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 dark:text-white/40">
                                @if ($s['last_date'])
                                    {{ $s['last_surah'] }} {{ $s['last_ayat'] }}
                                    <span class="text-xs text-slate-400 dark:text-white/30 ml-1">{{ \Carbon\Carbon::parse($s['last_date'])->diffForHumans() }}</span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-white/30">Belum ada setoran</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center mb-8">
        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-white/10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p class="text-slate-500 dark:text-white/40 font-medium">Belum ada siswa binaan</p>
        <p class="text-sm text-slate-400 dark:text-white/30 mt-1">Hubungi admin untuk penugasan siswa tahfidz.</p>
    </div>
    @endif

@endif

@if ($uniqueClasses->count() > 0)
<div class="mb-8">
    <h2 class="text-lg font-semibold mb-4">Kelas yang Diampu</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($uniqueClasses as $item)
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all duration-200 hover:shadow-lg hover:shadow-primary/5">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25">
                    <span class="text-sm font-bold text-white">{{ substr($item['class']->class_name, 0, 2) }}</span>
                </div>
                <div>
                    <h3 class="font-semibold">{{ $item['class']->class_name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-white/40">Tingkat {{ $item['class']->grade_level }}</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 dark:text-white/40">Siswa</span>
                    <span class="font-medium">{{ $item['student_count'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 dark:text-white/40">Mapel</span>
                    <span class="font-medium">{{ $item['subjects']->count() }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-white/5">
                    <p class="text-xs text-slate-400 dark:text-white/30 mb-1">Mata Pelajaran:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($item['subjects'] as $subject)
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $subject }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div>
    <h2 class="text-lg font-semibold mb-4">Akses Cepat</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @if ($isWaliKelas)
        <a href="{{ route('guru.absensi-kelas.index') }}" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary/100/10">
                    <svg class="w-5 h-5 text-primary dark:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="font-medium text-sm">Absensi Kelas</p>
                    <p class="text-xs text-slate-400 dark:text-white/30">Catat kehadiran</p>
                </div>
            </div>
        </a>
        @endif

        @if (!$isPJtahfidz)
        <a href="{{ route('guru.nilai.index') }}" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="font-medium text-sm">Input Nilai</p>
                    <p class="text-xs text-slate-400 dark:text-white/30">Kelola nilai siswa</p>
                </div>
            </div>
        </a>
        @endif

        @if ($isPJtahfidz)
        <a href="{{ route('guru.tahfidz.index') }}" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-500/10">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="font-medium text-sm">Input Hafalan</p>
                    <p class="text-xs text-slate-400 dark:text-white/30">Catat setoran tahfidz</p>
                </div>
            </div>
        </a>
        @endif

        @if ($isPJtahfidz)
        <a href="{{ route('guru.quran-absensi.rekap') }}" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="font-medium text-sm">Rekap Kehadiran</p>
                    <p class="text-xs text-slate-400 dark:text-white/30">Rekap absensi Al-Quran</p>
                </div>
            </div>
        </a>
        @endif
    </div>
</div>
@endsection
