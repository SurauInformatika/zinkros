@extends('layouts.app')

@section('title', 'Rekap Hafalan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Rekap Hafalan</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ auth()->user()->is_pj_tahfidz ? 'Rekap hafalan seluruh kelas Al-Quran.' : 'Ringkasan hafalan Al-Quran siswa per kelas.' }}</p>
    </div>
    @if ($classId)
    <a href="{{ route('guru.tahfidz.rekap-harian', ['class_id' => $classId]) }}"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-primary/20 hover:shadow-md transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Rekap Setoran Harian
    </a>
    @endif
</div>

<div class="mb-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
    <form action="{{ route('guru.tahfidz.rekap') }}" method="GET" class="flex flex-col sm:flex-row flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kelas</label>
            <select name="class_id" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#0f0f0f] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                @forelse ($plottedClasses as $pc)
                    <option value="{{ $pc['class']->id }}" {{ ($classId ?? null) == $pc['class']->id ? 'selected' : '' }}>{{ $pc['class']->class_name ?? $pc['class']->name }}</option>
                @empty
                    <option value="">Tidak ada kelas</option>
                @endforelse
            </select>
        </div>
        @if (auth()->user()->is_pj_tahfidz && isset($teachers) && $teachers->isNotEmpty())
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Guru Al-Quran</label>
            <select name="guru_id" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#0f0f0f] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="">Semua Guru Al-Quran</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" {{ ($filterTeacher ?? null) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </form>
</div>

@if ($classId && ($tab ?? 'per-siswa') === 'per-siswa')
    @if ($stats)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Hafalan</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Surah Dihafal</p>
            <p class="text-2xl font-bold">{{ $stats['unique_surahs'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Ziadah</p>
            <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['ziadah'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Murajaah</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['murajaah'] }}</p>
        </div>
    </div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        @php $avgScore = $studentStats->pluck('avg_score')->filter()->avg(); @endphp
        <h2 class="font-semibold">Rata-rata Skor: {{ $avgScore ? round($avgScore, 1) : '-' }} ({{ $avgScore ? \App\Models\TahfidzRecord::scoreToPredikat((int)$avgScore) : '-' }})</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                    @if (auth()->user()->is_pj_tahfidz && !empty($filterTeacher ?? null))
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                    @endif
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ziadah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Murajaah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ayat</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Surah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Rata Skor</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($studentStats as $sid => $ss)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-2.5 font-medium">
                        <a href="{{ route('guru.tahfidz.student', $sid) }}" class="text-primary dark:text-primary hover:underline">{{ $ss['name'] }}</a>
                    </td>
                    @if (auth()->user()->is_pj_tahfidz && !empty($filterTeacher ?? null))
                    <td class="px-4 py-2.5">{{ $ss['class_name'] ?? '-' }}</td>
                    @endif
                    <td class="px-4 py-2.5 text-center font-bold">{{ $ss['total'] }}</td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $ss['ziadah'] }}</span></td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">{{ $ss['murajaah'] }}</span></td>
                    <td class="px-4 py-2.5 text-center">{{ $ss['total_ayat'] }}</td>
                    <td class="px-4 py-2.5 text-center">{{ $ss['surahs'] }}</td>
                    <td class="px-4 py-2.5 text-center font-medium">{{ $ss['avg_score'] ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @php $p = isset($ss['avg_score']) ? \App\Models\TahfidzRecord::scoreToPredikat((int)$ss['avg_score']) : '-'; @endphp
                        @if ($p !== '-')
                            <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-xs font-bold">{{ $p }}</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ (auth()->user()->is_pj_tahfidz && !empty($filterTeacher ?? null)) ? 9 : 8 }}" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada data hafalan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
    @else
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p class="text-sm text-slate-500 dark:text-white/40">Belum ada data hafalan untuk kelas ini.</p>
    </div>
    @endif
@elseif ($classId && ($tab ?? 'per-siswa') === 'harian')
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
            <div>
                <h2 class="font-semibold">Rekap Setoran Harian</h2>
                <p class="text-xs text-slate-500 dark:text-white/40 mt-1">{{ $selectedClassRoom?->class_name ?? '' }}</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Total Siswa</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Sudah Setor</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Belum Setor</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Kelengkapan</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailyRekap as $row)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5 font-medium">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d F Y') }}</td>
                        <td class="px-4 py-2.5 text-center">{{ $row['total'] }}</td>
                        <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ $row['sudah'] }}</span></td>
                        <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">{{ $row['belum'] }}</span></td>
                        <td class="px-4 py-2.5 text-center font-medium">{{ $row['pct'] }}%</td>
                        <td class="px-4 py-2.5 text-center">
                            <button type="button" onclick="openDailyDetail('{{ $row['date'] }}')" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 dark:border-white/10 px-2.5 py-1 text-xs hover:bg-slate-50 dark:hover:bg-white/5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada setoran hafalan pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="daily-detail-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeDailyDetail()"></div>
        <div class="relative mx-auto mt-10 max-w-6xl w-[95%] rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-white/5">
                <div>
                    <h3 class="font-semibold">Detail Setoran Harian</h3>
                    <p class="text-xs text-slate-500 dark:text-white/40" id="daily-detail-title"></p>
                </div>
                <button type="button" onclick="closeDailyDetail()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                            <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ziadah</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Murajaah</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ayat</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Surah</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Jam Setor</th>
                            <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="daily-detail-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const dailyStudentsMap = @json($dailyStudentsMap);
        function openDailyDetail(date) {
            const modal = document.getElementById('daily-detail-modal');
            const title = document.getElementById('daily-detail-title');
            const body = document.getElementById('daily-detail-body');
            title.textContent = new Date(date + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            body.innerHTML = '';
            const list = dailyStudentsMap[date] || [];
            if (list.length === 0) {
                body.innerHTML = '<tr><td colspan="8" class="px-4 py-6 text-center text-slate-400">Tidak ada data</td></tr>';
            } else {
                list.forEach(s => {
                    let statusBadge = '';
                    if (s.status === 'sudah') statusBadge = '<span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">Sudah Setor</span>';
                    else if (s.status === 'SAKIT') statusBadge = '<span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">Sakit</span>';
                    else if (s.status === 'IZIN') statusBadge = '<span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">Izin</span>';
                    else if (s.status === 'ALPA') statusBadge = '<span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-400">Alpa</span>';
                    else statusBadge = '<span class="inline-flex items-center rounded-md bg-slate-50 dark:bg-white/5 px-2 py-0.5 text-xs font-medium text-slate-600 dark:text-white/60">Belum Setor</span>';
                    let action = s.status === 'sudah' ? `<a href="{{ route('guru.tahfidz.student', ':id') }}".replace(':id', s.id) class="inline-flex items-center rounded-lg border border-slate-200 dark:border-white/10 px-2.5 py-1 text-xs hover:bg-slate-50 dark:hover:bg-white/5">Lihat Setoran</a>` : `<a href="{{ route('guru.quran-hafalan.students') }}?class_id={{ $classId }}&date=${date}" class="inline-flex items-center rounded-lg bg-primary text-white px-2.5 py-1 text-xs hover:opacity-90">Input Hafalan</a>`;
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]';
                    tr.innerHTML = `<td class="px-4 py-2.5 font-medium">${s.name}</td><td class="px-4 py-2.5 text-center">${statusBadge}</td><td class="px-4 py-2.5 text-center">${s.ziadah}</td><td class="px-4 py-2.5 text-center">${s.murajaah}</td><td class="px-4 py-2.5 text-center">${s.ayat}</td><td class="px-4 py-2.5">${s.surah}</td><td class="px-4 py-2.5 text-center">${s.time}</td><td class="px-4 py-2.5 text-center">${action}</td>`;
                    body.appendChild(tr);
                });
            }
            modal.classList.remove('hidden');
        }
        function closeDailyDetail() {
            document.getElementById('daily-detail-modal').classList.add('hidden');
        }
    </script>
@elseif ($classId)
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    <p class="text-sm text-slate-500 dark:text-white/40">Belum ada data hafalan untuk kelas ini.</p>
</div>
@endif
@endsection
