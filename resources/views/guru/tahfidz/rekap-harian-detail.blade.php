@extends('layouts.app')

@section('title', 'Detail Rekap Setoran')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Detail Setoran Harian</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
            {{ $classRoom->class_name }} · {{ \Carbon\Carbon::parse($detailDate)->translatedFormat('l, d M Y') }}
        </p>
    </div>
    <a href="{{ route('guru.tahfidz.rekap-harian', ['class_id' => $classRoom->id, 'filter' => $filter]) }}"
        class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium transition hover:bg-slate-50 dark:border-white/10 dark:hover:bg-white/5">
        Kembali ke Rekap
    </a>
</div>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414]">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 dark:border-white/5 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kehadiran</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Setoran</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detailStudents as $item)
                <tr class="border-b border-slate-50 last:border-0 dark:border-white/5">
                    <td class="px-4 py-3 font-medium">{{ $item['student']->name }}</td>
                    <td class="px-4 py-3">
                        <span @class([
                            'inline-flex rounded-md px-2 py-0.5 text-xs font-medium',
                            'bg-primary/10 text-primary' => $item['status'] === 'HADIR',
                            'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' => $item['status'] === 'ALPA',
                            'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' => $item['status'] === 'SAKIT',
                            'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' => $item['status'] === 'IZIN',
                        ])>
                            {{ $item['status'] === 'HADIR' ? 'Hadir' : \App\Models\TahfidzRecord::attendanceDisplayLabel($item['status']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @forelse ($item['records'] as $record)
                            <div>
                                {{ $record->quranMaster?->surah_name ?? '-' }}
                                ({{ $record->ayat_start }}–{{ $record->ayat_end }})
                                · {{ $record->activity_type === \App\Models\TahfidzRecord::TYPE_ZIADAH ? 'Ziadah' : 'Murajaah' }}
                                @if ($record->score !== null)
                                    · Skor {{ $record->score }}
                                @endif
                            </div>
                        @empty
                            <span class="text-slate-400 dark:text-white/30">-</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $item['notes'] ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Tidak ada data siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
