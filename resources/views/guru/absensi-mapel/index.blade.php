@extends('layouts.app')

@section('title', 'Absensi Mapel')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Absensi Mapel</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Pilih kelas dan mata pelajaran untuk mengisi absensi.</p>
</div>

@if (session('success'))
<div class="mb-6 rounded-xl border border-primary/20 bg-primary/10 p-4 text-sm text-primary dark:border-primary/30 dark:bg-primary/100/10 dark:text-primary">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

@if ($plottedClasses->isEmpty())
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-white/10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    <p class="text-slate-500 dark:text-white/40 font-medium">Belum ada kelas yang diplot</p>
    <p class="text-sm text-slate-400 dark:text-white/30 mt-1">Hubungi admin untuk plotting kelas dan mata pelajaran.</p>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach ($plottedClasses as $plot)
    @foreach ($plot['subjects'] as $subjectId => $subject)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all duration-200 hover:shadow-lg hover:shadow-primary/5">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25">
                <span class="text-sm font-bold text-white">{{ substr($plot['class']->class_name, 0, 2) }}</span>
            </div>
            <div>
                <h3 class="font-semibold">{{ $plot['class']->class_name }}</h3>
                <p class="text-xs text-slate-500 dark:text-white/40">{{ $subject->name }}</p>
            </div>
        </div>
        <a href="{{ route('guru.absensi-mapel.create', ['class_id' => $plot['class']->id, 'subject_id' => $subjectId]) }}"
           class="block w-full rounded-lg bg-primary/100 hover:bg-primary text-white text-center py-2.5 text-sm font-medium transition-colors">
            Isi Absensi Hari Ini
        </a>
    </div>
    @endforeach
    @endforeach
</div>
@endif

@if ($recentAbsensi->isNotEmpty())
<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Rekap Hari Ini ({{ $today }})</h2>
        <a href="{{ route('guru.absensi-mapel.history') }}" class="text-sm text-primary dark:text-primary hover:underline">Lihat Semua Riwayat</a>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Total</th>
                        <th class="px-4 py-3 text-center font-medium text-primary dark:text-primary">Hadir</th>
                        <th class="px-4 py-3 text-center font-medium text-blue-600 dark:text-blue-400">Izin</th>
                        <th class="px-4 py-3 text-center font-medium text-amber-600 dark:text-amber-400">Sakit</th>
                        <th class="px-4 py-3 text-center font-medium text-red-600 dark:text-red-400">Alpa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentAbsensi as $row)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                        <td class="px-4 py-3 font-medium">{{ $row['subject'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $row['count'] }}</td>
                        <td class="px-4 py-3 text-center text-primary dark:text-primary">{{ $row['hadir'] }}</td>
                        <td class="px-4 py-3 text-center text-blue-600 dark:text-blue-400">{{ $row['izin'] }}</td>
                        <td class="px-4 py-3 text-center text-amber-600 dark:text-amber-400">{{ $row['sakit'] }}</td>
                        <td class="px-4 py-3 text-center text-red-600 dark:text-red-400">{{ $row['alpa'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
