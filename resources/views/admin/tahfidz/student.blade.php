@extends('layouts.app')

@section('title', 'Detail Hafalan — ' . $student->name)

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route($routeGroup . '.index', ['class_id' => $student->class_id]) }}" class="text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold tracking-tight">{{ $student->name }}</h1>
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
        {{ $student->classRoom?->class_name ?? '-' }} — Detail hafalan tahfidz
    </p>
</div>

@if ($stats)
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Hafalan</p>
        <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Ayat Baru (Ziadah)</p>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['ziadah_ayat'] }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Ayat Ulangan (Murajaah)</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['murajaah_ayat'] }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Surah</p>
        <p class="text-2xl font-bold">
            <span class="text-primary dark:text-primary">{{ $stats['surahs_hafal'] }}</span>
            <span class="text-slate-300 dark:text-white/20">/</span>
            <span class="text-amber-500 dark:text-amber-400">{{ $stats['surahs_progress'] }}</span>
        </p>
        <p class="text-[10px] text-slate-400 dark:text-white/30 mt-0.5">Hafal / Progress</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Rata-rata Skor</p>
        @php $predikat = \App\Models\TahfidzRecord::scoreToPredikat((int)($stats['avg_score'] ?? 0)); @endphp
        <p class="text-2xl font-bold">
            {{ $stats['avg_score'] ?? '-' }}
            <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($predikat) }} px-2 py-0.5 text-sm font-bold ml-1">{{ $predikat }}</span>
        </p>
    </div>
</div>

@if ($stats['hafal_names']->isNotEmpty() || $stats['progress_list']->isNotEmpty())
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 mb-6 space-y-3">
    @if ($stats['hafal_names']->isNotEmpty())
    <div>
        <p class="text-xs text-primary dark:text-primary font-medium mb-2">Sudah Dihafal ({{ $stats['surahs_hafal'] }})</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($stats['hafal_names'] as $surah)
            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">{{ $surah }}</span>
            @endforeach
        </div>
    </div>
    @endif
    @if ($stats['progress_list']->isNotEmpty())
    <div>
        <p class="text-xs text-amber-600 dark:text-amber-400 font-medium mb-2">Sedang Berlangsung ({{ $stats['surahs_progress'] }})</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($stats['progress_list'] as $prog)
            <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">{{ $prog['name'] }} <span class="ml-1 text-[10px] opacity-70">{{ $prog['current'] }}/{{ $prog['total'] }}</span></span>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 mb-6">
    <h2 class="font-semibold mb-4">Grafik Hafalan</h2>

<div class="flex flex-wrap items-center gap-2 mb-5">
        <label for="chartFilter" class="text-xs font-medium text-slate-500 dark:text-white/40">Rentang</label>
        <select id="chartFilter" onchange="loadChart(this.value)"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            <option value="pekan">1 Pekan</option>
            <option value="pekan_lalu">Pekan Lalu</option>
            <option value="bulan">Bulan Ini</option>
            <option value="bulan_lalu">Bulan Lalu</option>
            <option value="semester">Semester Ini</option>
            <option value="semester_lalu">Semester Lalu</option>
            <option value="ta_init" selected>TA Ini</option>
            <option value="ta_lalu">TA Lalu</option>
        </select>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <div class="lg:col-span-2 rounded-lg border border-slate-100 dark:border-white/5 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-2">Ayat per Hari</p>
            <div style="height:220px"><canvas id="lineChart"></canvas></div>
        </div>
        <div class="rounded-lg border border-slate-100 dark:border-white/5 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-2">Ziadah vs Murajaah</p>
            <div style="height:220px"><canvas id="donutChart"></canvas></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 rounded-lg border border-slate-100 dark:border-white/5 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-2">Rata-rata Skor</p>
            <div style="height:180px"><canvas id="barChart"></canvas></div>
        </div>
        <div class="rounded-lg border border-slate-100 dark:border-white/5 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-3">Ringkasan</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Hafalan</span><span class="font-bold" id="statTotal">0</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Ayat</span><span class="font-bold" id="statAyat">0</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Surah</span><span class="font-bold" id="statSurah">0</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Ziadah</span><span class="font-bold text-primary dark:text-primary" id="statZiadah">0</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Murajaah</span><span class="font-bold text-blue-600 dark:text-blue-400" id="statMurajaah">0</span></div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Riwayat Hafalan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Surah</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Ayat</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Jenis</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Grade</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Guru</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $rec)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="px-4 py-2.5 text-sm">{{ \Carbon\Carbon::parse($rec->recorded_date)->format('d M Y') }}</td>
                    <td class="px-4 py-2.5 font-medium">{{ $rec->quranMaster->surah_number }}. {{ $rec->quranMaster->surah_name }}</td>
                    <td class="px-4 py-2.5 text-center">{{ $rec->ayat_start }} — {{ $rec->ayat_end }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($rec->activity_type === 'ZIADAH')
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Ziadah</span>
                        @else
                        <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">Murajaah</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @php $p = \App\Models\TahfidzRecord::scoreToPredikat($rec->score); @endphp
                        <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-xs font-bold">{{ $rec->score }} ({{ $p }})</span>
                    </td>
                    <td class="px-4 py-2.5 text-sm text-slate-500 dark:text-white/40">{{ $rec->teacher->name ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-sm text-slate-400 dark:text-white/30">{{ $rec->notes ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada riwayat hafalan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartUrl = '{{ route($routeGroup . ".chart", $student->id) }}';
let lineChart, donutChart, barChart;

function initCharts() {
    const isDark = document.documentElement.classList.contains('dark');
    const grid = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
    const txt = isDark ? 'rgba(255,255,255,0.4)' : 'rgba(0,0,0,0.4)';
    Chart.defaults.color = txt;
    Chart.defaults.borderColor = grid;

    lineChart = new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'Ziadah (Ayat Baru)', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointBackgroundColor: '#10b981' },
                { label: 'Murajaah (Ulangan)', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointBackgroundColor: '#3b82f6' }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    donutChart = new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: { labels: ['Ziadah', 'Murajaah'], datasets: [{ data: [0, 0], backgroundColor: ['#10b981', '#3b82f6'], borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle' } } } }
    });

    barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: { labels: ['Rata-rata Skor'], datasets: [{ data: [0], backgroundColor: ['#10b981'], borderRadius: 6, barThickness: 40 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
    });
}

function loadChart(filter) {
    fetch(chartUrl + '?filter=' + filter)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            lineChart.data.labels = d.labels;
            lineChart.data.datasets[0].data = d.ayat_ziadah;
            lineChart.data.datasets[1].data = d.ayat_murajaah;
            lineChart.update();
            donutChart.data.datasets[0].data = [d.stats.ziadah, d.stats.murajaah];
            donutChart.update();
            barChart.data.datasets[0].data = [d.stats.avg_score || 0];
            barChart.update();
            document.getElementById('statTotal').textContent = d.stats.total;
            document.getElementById('statAyat').textContent = d.stats.total_ayat;
            document.getElementById('statSurah').textContent = d.stats.surahs;
            document.getElementById('statZiadah').textContent = d.stats.ziadah;
            document.getElementById('statMurajaah').textContent = d.stats.murajaah;
        });
}

document.addEventListener('DOMContentLoaded', function() { initCharts(); loadChart('ta_init'); });
</script>
@endpush
