@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('guru.absensi-mapel.rekap') }}" class="mb-2 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-primary dark:text-white/40 dark:hover:text-primary">
            ← Kembali ke Daftar
        </a>
        <h1 class="text-2xl font-bold tracking-tight">Detail Absensi</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }} · {{ $subject->name }} · {{ $classRoom->class_name }}
        </p>
        @if ($topic)
            <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Topik: {{ $topic }}</p>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center dark:border-white/10 dark:bg-[#141414]">
            <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['total'] }}</div>
            <div class="mt-1 text-xs text-slate-500 dark:text-white/40">Total</div>
        </div>
        <div class="rounded-xl border border-primary/20 bg-primary/10 p-4 text-center dark:border-primary/20 dark:bg-primary/100/5">
            <div class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['hadir'] }}</div>
            <div class="mt-1 text-xs text-primary dark:text-primary/60">Hadir</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center dark:border-amber-500/20 dark:bg-amber-500/5">
            <div class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $stats['sakit'] }}</div>
            <div class="mt-1 text-xs text-amber-600 dark:text-amber-400/60">Sakit</div>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center dark:border-blue-500/20 dark:bg-blue-500/5">
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['izin'] }}</div>
            <div class="mt-1 text-xs text-blue-600 dark:text-blue-400/60">Izin</div>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center dark:border-red-500/20 dark:bg-red-500/5">
            <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['alpa'] }}</div>
            <div class="mt-1 text-xs text-red-600 dark:text-red-400/60">Alpa</div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">#</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NISN</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $i => $s)
                        <tr class="border-b border-slate-100 last:border-0 dark:border-white/5">
                            <td class="px-4 py-3 text-slate-400 dark:text-white/30">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-white/80">{{ $s['name'] }}</td>
                            <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $s['nisn'] }}</td>
                            <td class="px-4 py-3">
                                @if ($s['status'] === 'HADIR')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary dark:bg-primary/100/10 dark:text-primary">Hadir</span>
                                @elseif ($s['status'] === 'SAKIT')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Sakit</span>
                                @elseif ($s['status'] === 'IZIN')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Izin</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Alpa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 dark:text-white/40">{{ $s['notes'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Tidak ada data absensi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
