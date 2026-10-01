@extends('layouts.app')

@section('title', $student->name . ' — Kehadiran ' . \Carbon\Carbon::parse($date)->translatedFormat('d M Y'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-absensi.detail', $date) }}" class="text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $student->name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $student->classRoom?->class_name ?? '-' }} — {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Status Hari Ini</p>
            @php
                $statusCls = match($status) {
                    'HADIR' => 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary',
                    'SAKIT' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
                    'IZIN' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
                    default => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
                };
            @endphp
            <p class="text-lg font-bold"><span class="inline-flex items-center rounded-md {{ $statusCls }} px-2.5 py-0.5 text-sm font-medium">{{ $status }}</span></p>
        </div>
        @if ($hafalan)
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Surah</p>
            <p class="text-lg font-bold">{{ $hafalan }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Ayat</p>
            <p class="text-lg font-bold">{{ $ayatRange ?? '-' }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Skor</p>
            @if ($predikat)
            <p class="text-lg font-bold">
                {{ $score }}
                <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($predikat) }} px-2 py-0.5 text-xs font-bold ml-1">{{ $predikat }}</span>
            </p>
            @else
            <p class="text-lg font-bold">-</p>
            @endif
        </div>
        @endif
    </div>

    @if ($hafalan && $activityType)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center gap-3">
            <div class="text-sm text-slate-500 dark:text-white/40">Jenis Kegiatan:</div>
            @if ($activityType === 'ZIADAH')
                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">Ziadah (Ayat Baru)</span>
            @else
                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-400">Murajaah (Ulangan)</span>
            @endif
        </div>
    </div>
    @endif

    @if ($statsArr && $statsArr['total'] > 0)
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Hafalan</p>
            <p class="text-2xl font-bold">{{ $statsArr['total'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Ayat Baru (Ziadah)</p>
            <p class="text-2xl font-bold text-primary dark:text-primary">{{ $statsArr['ziadah_ayat'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Ayat Ulangan (Murajaah)</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $statsArr['murajaah_ayat'] }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Surah</p>
            <p class="text-2xl font-bold">
                <span class="text-primary dark:text-primary">{{ $statsArr['surahs_hafal'] }}</span>
                <span class="text-slate-300 dark:text-white/20">/</span>
                <span class="text-amber-500 dark:text-amber-400">{{ $statsArr['surahs_progress'] }}</span>
            </p>
            <p class="text-[10px] text-slate-400 dark:text-white/30 mt-0.5">Hafal / Progress</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Rata-rata Skor</p>
            @php $p = \App\Models\TahfidzRecord::scoreToPredikat((int)($statsArr['avg_score'] ?? 0)); @endphp
            <p class="text-2xl font-bold">
                {{ $statsArr['avg_score'] }}
                <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-sm font-bold ml-1">{{ $p }}</span>
            </p>
        </div>
    </div>

    @if ($statsArr['hafal_names']->isNotEmpty() || $statsArr['progress_list']->isNotEmpty())
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 space-y-3">
        @if ($statsArr['hafal_names']->isNotEmpty())
        <div>
            <p class="text-xs text-primary dark:text-primary font-medium mb-2">Sudah Dihafal ({{ $statsArr['surahs_hafal'] }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($statsArr['hafal_names'] as $surah)
                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">{{ $surah }}</span>
                @endforeach
            </div>
        </div>
        @endif
        @if ($statsArr['progress_list']->isNotEmpty())
        <div>
            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium mb-2">Sedang Berlangsung ({{ $statsArr['surahs_progress'] }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($statsArr['progress_list'] as $prog)
                <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">{{ $prog['name'] }} <span class="ml-1 text-[10px] opacity-70">{{ $prog['current'] }}/{{ $prog['total'] }}</span></span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Grafik Hafalan</h2>
            <div>
                <label for="chartFilter" class="sr-only">Rentang grafik</label>
                <select id="chartFilter" onchange="loadChart(this.value)"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
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
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Hafalan</span><span class="font-bold" id="chartTotal">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Ayat</span><span class="font-bold" id="chartAyat">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Surah</span><span class="font-bold" id="chartSurah">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Ziadah</span><span class="font-bold text-primary dark:text-primary" id="chartZiadah">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Murajaah</span><span class="font-bold text-blue-600 dark:text-blue-400" id="chartMurajaah">0</span></div>
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
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allRecords as $rec)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 {{ $rec->recorded_date === $date ? 'bg-primary/10/50 dark:bg-primary/100/5' : '' }}">
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
                            @php $gp = \App\Models\TahfidzRecord::scoreToPredikat($rec->score); @endphp
                            <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($gp) }} px-2 py-0.5 text-xs font-bold">{{ $rec->score }} ({{ $gp }})</span>
                        </td>
                        <td class="px-4 py-2.5 text-sm text-slate-400 dark:text-white/30">{{ $rec->notes ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada riwayat hafalan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        var chartUrl = '{{ route("guru.quran-absensi.student-chart-data", $student->id) }}';
        var lineChart, donutChart, barChart;

        function initCharts() {
            var isDark = document.documentElement.classList.contains('dark');
            var grid = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
            var txt = isDark ? 'rgba(255,255,255,0.4)' : 'rgba(0,0,0,0.4)';
            Chart.defaults.color = txt;
            Chart.defaults.borderColor = grid;

            lineChart = new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: { labels: [], datasets: [
                    { label: 'Ziadah (Ayat Baru)', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointBackgroundColor: '#10b981' },
                    { label: 'Murajaah (Ulangan)', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointBackgroundColor: '#3b82f6' }
                ] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });

            donutChart = new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: { labels: ['Ziadah', 'Murajaah'], datasets: [{ data: [0, 0], backgroundColor: ['#10b981', '#3b82f6'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle' } } } }
            });

            barChart = new Chart(document.getElementById('barChart'), {
                type: 'bar',
                data: { labels: [], datasets: [{ label: 'Skor', data: [], backgroundColor: 'rgba(16,185,129,0.6)', borderRadius: 4, barThickness: 20 }] },
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
                    barChart.data.labels = d.labels;
                    barChart.data.datasets[0].data = d.scores;
                    barChart.update();
                    document.getElementById('chartTotal').textContent = d.stats.total;
                    document.getElementById('chartAyat').textContent = d.stats.total_ayat;
                    document.getElementById('chartSurah').textContent = d.stats.surahs;
                    document.getElementById('chartZiadah').textContent = d.stats.ziadah;
                    document.getElementById('chartMurajaah').textContent = d.stats.murajaah;
                });
        }

        document.addEventListener('DOMContentLoaded', function() { initCharts(); loadChart('ta_init'); });
    </script>
@endpush
@endsection
