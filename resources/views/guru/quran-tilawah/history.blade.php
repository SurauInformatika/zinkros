@extends('layouts.app')

@section('title', 'Riwayat Tilawah — ' . $student->name)

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-tilawah.students') }}" class="rounded-lg border border-slate-200 dark:border-white/10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white/60 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Riwayat Tilawah</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $student->name }} — {{ $student->classRoom?->class_name ?? '-' }}</p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <h2 class="font-semibold mb-4">Grafik Tilawah (Halaman)</h2>

        <div class="flex flex-wrap items-center gap-2 mb-5">
            <label for="chartFilter" class="text-xs font-medium text-slate-500 dark:text-white/40">Rentang</label>
            <select id="chartFilter" onchange="loadChart(this.value)"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                <option value="pekan">1 Pekan</option>
                <option value="bulan">Bulan Ini</option>
                <option value="semester">Semester Ini</option>
                <option value="ta_init" selected>TA Ini</option>
                <option value="ta_lalu">TA Lalu</option>
            </select>
            <div class="flex w-full flex-col gap-1.5 sm:ml-2 sm:w-auto sm:flex-row sm:items-center">
                <div class="flex flex-1 items-center gap-1.5">
                    <input type="date" id="dateFrom" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2.5 py-1.5 text-xs sm:w-auto dark:text-white/80 focus:border-primary focus:ring-primary/20">
                    <span class="text-xs text-slate-400 dark:text-white/30">—</span>
                    <input type="date" id="dateTo" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2.5 py-1.5 text-xs sm:w-auto dark:text-white/80 focus:border-primary focus:ring-primary/20">
                </div>
                <button onclick="loadChartCustom()" class="w-full px-3 py-1.5 text-xs font-medium rounded-lg bg-primary/100 hover:bg-primary text-white transition sm:w-auto">Terapkan</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <div class="lg:col-span-2 rounded-lg border border-slate-100 dark:border-white/5 p-4">
                <p class="text-xs text-slate-500 dark:text-white/40 mb-2">Halaman per Hari</p>
                <div style="height:220px"><canvas id="lineChart"></canvas></div>
            </div>
            <div class="rounded-lg border border-slate-100 dark:border-white/5 p-4">
                <p class="text-xs text-slate-500 dark:text-white/40 mb-3">Ringkasan</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Pertemuan</span><span class="font-bold" id="statTotal">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Halaman</span><span class="font-bold text-primary dark:text-primary" id="statPages">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Jenjang Dilalui</span><span class="font-bold" id="statLevels">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Rata-rata Skor</span><span class="font-bold" id="statAvgScore">0</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- History table --}}
    @if ($records->count() > 0)
        @php $grouped = $records->groupBy(fn($r) => \Carbon\Carbon::parse($r->recorded_date)->format('d M Y')); @endphp
        @foreach ($grouped as $date => $dayRecords)
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">{{ $date }}</h2>
                    <span class="text-xs text-slate-400 dark:text-white/30">{{ $dayRecords->count() }}x input</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-white/5">
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Jenjang</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40">Halaman</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40">Skor</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayRecords as $i => $rec)
                                <tr class="border-b border-slate-50 last:border-0 dark:border-white/5">
                                    <td class="px-4 py-2.5 text-slate-400 dark:text-white/30">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($rec->reading_level_id)
                                            <span class="font-medium text-slate-700 dark:text-white/80">{{ $rec->readingLevel->label }}</span>
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-white/30">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-slate-600 dark:text-white/70">{{ $rec->reading_level_id ? $rec->page_start . '-' . $rec->page_end : '-' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if ($rec->score > 0)
                                            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary px-2 py-0.5 text-xs font-bold">{{ $rec->score }}</span>
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-white/30">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if ($rec->status)
                                            <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 px-2 py-0.5 text-xs font-medium">{{ $rec->status }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary px-2 py-0.5 text-xs font-medium">HADIR</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-400 dark:text-white/30">{{ $rec->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center dark:border-white/10 dark:bg-[#141414]">
            <p class="text-sm text-slate-400 dark:text-white/30">Belum ada riwayat tilawah untuk siswa ini.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const chartUrl = '{{ route("guru.quran-tilawah.chart", $student->id) }}';
let lineChart, chartData = {};

function initChart() {
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
                { label: 'Halaman', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#10b981' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(20,20,20,0.95)' : 'rgba(255,255,255,0.95)',
                    titleColor: isDark ? 'rgba(255,255,255,0.8)' : 'rgba(0,0,0,0.8)',
                    bodyColor: isDark ? 'rgba(255,255,255,0.6)' : 'rgba(0,0,0,0.6)',
                    borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        title: function(items) {
                            var idx = items[0].dataIndex;
                            var date = chartData.labels[idx] || '';
                            var count = chartData.counts ? chartData.counts[idx] : 0;
                            return date + '  (' + count + 'x input)';
                        },
                        afterBody: function(items) {
                            var idx = items[0].dataIndex;
                            if (chartData.avg_scores && chartData.avg_scores[idx] !== null) {
                                return ['Skor rata-rata: ' + chartData.avg_scores[idx]];
                            }
                            return [];
                        }
                    }
                }
            },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

function updateStats(d) {
    document.getElementById('statTotal').textContent = d.stats.total;
    document.getElementById('statPages').textContent = d.stats.pages;
    document.getElementById('statLevels').textContent = d.stats.levels;
    document.getElementById('statAvgScore').textContent = d.stats.avg_score || 0;
}

function loadChart(filter) {
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';

    fetch(chartUrl + '?filter=' + filter)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            chartData = d;
            lineChart.data.labels = d.labels;
            lineChart.data.datasets[0].data = d.pages;
            lineChart.update();
            updateStats(d);
        });
}

function loadChartCustom() {
    var from = document.getElementById('dateFrom').value;
    var to = document.getElementById('dateTo').value;
    if (!from || !to) return;

    fetch(chartUrl + '?date_from=' + from + '&date_to=' + to)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            chartData = d;
            lineChart.data.labels = d.labels;
            lineChart.data.datasets[0].data = d.pages;
            lineChart.update();
            updateStats(d);
        });
}

document.addEventListener('DOMContentLoaded', function() { initChart(); loadChart('ta_init'); });
</script>
@endpush
