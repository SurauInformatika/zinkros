@extends('layouts.app')

@section('title', 'Absensi Al-Quran')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Absensi Al-Quran</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Isi absensi hafalan Al-Quran</p>
    </div>

    <form method="GET" action="{{ route('guru.quran-absensi.index') }}" class="flex items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
        </div>
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Lihat</button>
        <a href="{{ route('guru.quran-absensi.create', ['date' => $date]) }}" class="rounded-lg bg-primary/100 px-4 py-2 text-sm font-medium text-white hover:bg-primary">Isi Absensi</a>
    </form>

    @if ($assignments->count() > 0)
        @foreach ($assignments as $className => $group)
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">{{ $className }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-white/5">
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group as $i => $a)
                                <tr class="border-b border-slate-50 last:border-0 dark:border-white/5">
                                    <td class="px-4 py-2 text-slate-400 dark:text-white/30">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-slate-700 dark:text-white/80">{{ $a->student?->name ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @php $status = $existingAttendance->get($a->student_id); @endphp
                                        @if ($status)
                                            @if ($status === 'HADIR')
                                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary dark:bg-primary/100/10 dark:text-primary">Hadir</span>
                                            @elseif ($status === 'SAKIT')
                                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Sakit</span>
                                            @elseif ($status === 'IZIN')
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Izin</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Alpa</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-white/30">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center dark:border-white/10 dark:bg-[#141414]">
            <p class="text-sm text-slate-400 dark:text-white/30">Anda belum memiliki siswa Al-Quran yang di-assign.</p>
        </div>
    @endif
</div>
@endsection
