@extends('layouts.app')

@section('title', 'Hafalan Tahfidz')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Hafalan Tahfidz</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Catat hafalan Al-Quran siswa.</p>
</div>

@if (session('success'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @forelse ($plottedClasses as $pc)
        @foreach ($pc['subjects'] as $subject)
        <a href="{{ route('guru.tahfidz.create', ['class_id' => $pc['class']->id, 'subject_id' => $subject->id]) }}"
            class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 hover:border-primary/30 dark:hover:border-primary/30 transition-all group">
            <div class="flex items-center justify-between mb-2">
                <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-2 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-400">{{ $pc['class']->class_name }}</span>
                <svg class="w-4 h-4 text-slate-400 dark:text-white/30 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
            <p class="text-sm font-medium">{{ $subject->name }}</p>
            <p class="text-xs text-slate-400 dark:text-white/30 mt-1">Al-Quran</p>
        </a>
        @endforeach
    @empty
    <div class="col-span-full rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p class="text-sm text-slate-500 dark:text-white/40">Belum terploting mengajar Al-Quran.</p>
    </div>
    @endforelse
</div>

@if ($recentRecords->isNotEmpty())
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Hafalan Hari Ini — {{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ziadah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Murajaah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentRecords as $rec)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="px-4 py-2.5 font-medium">{{ $rec['student_name'] }}</td>
                    <td class="px-4 py-2.5 text-center">{{ $rec['count'] }}</td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $rec['ziadah'] }}</span></td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">{{ $rec['murajaah'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="flex justify-end">
    <a href="{{ route('guru.tahfidz.rekap') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Rekap Hafalan
    </a>
</div>
@endsection
