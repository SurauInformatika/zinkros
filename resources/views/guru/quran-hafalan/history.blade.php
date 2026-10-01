@extends('layouts.app')

@section('title', 'Riwayat Hafalan — ' . $student->name)

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-hafalan.students') }}" class="rounded-lg border border-slate-200 dark:border-white/10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white/60 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Riwayat Hafalan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $student->name }} — {{ $student->classRoom?->class_name ?? '-' }}</p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
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
                <p class="text-xs text-slate-500 dark:text-white/40 mb-2">Ayat per Hari</p>
                <div style="height:220px"><canvas id="lineChart"></canvas></div>
            </div>
            <div class="rounded-lg border border-slate-100 dark:border-white/5 p-4">
                <p class="text-xs text-slate-500 dark:text-white/40 mb-3">Ringkasan</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Total Hafalan</span><span class="font-bold" id="statTotal">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Surah</span><span class="font-bold" id="statSurah">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Ziadah</span><span class="font-bold text-primary dark:text-primary" id="statZiadah">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Murajaah</span><span class="font-bold text-blue-600 dark:text-blue-400" id="statMurajaah">0</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Rata-rata Skor</span><span class="font-bold" id="statAvgScore">0</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Target Hafalan --}}
    @if (!empty($targets))
    <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Target Hafalan</h2>
            <a href="{{ route('guru.quran-hafalan.target-create', $student->id) }}" class="text-xs font-medium text-primary hover:text-primary dark:text-primary">+ Tambah Target</a>
        </div>
        @foreach ($targets as $target)
        <div class="rounded-lg border border-slate-200 dark:border-white/10 p-4 mb-3 last:mb-0">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $target['title'] }}</span>
                    @php
                        $stBadge = match($target['status']) {
                            'completed' => 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary',
                            'overdue' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
                            default => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
                        };
                        $stLabel = match($target['status']) {
                            'completed' => 'Selesai',
                            'overdue' => 'Lewat Deadline',
                            default => 'On Track',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md {{ $stBadge }} px-2 py-0.5 text-xs font-medium">{{ $stLabel }}</span>
                </div>
                <span class="text-sm font-bold {{ $target['status'] === 'completed' ? 'text-primary dark:text-primary' : ($target['status'] === 'overdue' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">{{ $target['percent'] }}%</span>
            </div>
            <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
                <div class="h-full rounded-full {{ $target['status'] === 'completed' ? 'bg-primary/100' : ($target['status'] === 'overdue' ? 'bg-red-500' : 'bg-primary/100/80') }}"
                    style="width: {{ min(100, $target['percent']) }}%"></div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 mt-2 text-xs text-slate-400 dark:text-white/30">
                <span>{{ $target['completed_ayat'] }}/{{ $target['total_ayat'] }} ayat • {{ \Carbon\Carbon::parse($target['target_date'])->format('d M Y') }}</span>
                <div class="flex items-center gap-2">
                    <span class="mr-1">{{ $target['days_remaining'] >= 0 ? 'Sisa ' . $target['days_remaining'] . ' hari' : 'Lewat ' . abs($target['days_remaining']) . ' hari' }}</span>
                    <a href="{{ route('guru.quran-hafalan.target-edit', [$student->id, $target['id']]) }}" title="Edit target" class="rounded-lg p-1.5 text-primary/80 hover:text-primary hover:bg-primary/10 dark:text-primary dark:hover:text-primary dark:hover:bg-primary/20 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('guru.quran-hafalan.target-destroy', [$student->id, $target['id']]) }}" onsubmit="return confirm('Hapus target ini?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Hapus target" class="rounded-lg p-1.5 text-red-500/80 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <button type="button" class="mt-3 inline-flex items-center gap-1 text-xs text-slate-500 dark:text-white/40 hover:text-slate-700 dark:hover:text-white/60 target-toggle">
                <svg class="h-3 w-3 transition-transform target-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                Detail per surah
            </button>
            <div class="target-detail hidden mt-2 border-t border-slate-100 dark:border-white/5 pt-2 space-y-1">
                @foreach ($target['items'] as $item)
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="text-slate-500 dark:text-white/40">
                        {{ $item['surah_number'] }}. {{ $item['surah_name'] }}
                        <span class="text-slate-400 dark:text-white/30">ayat {{ $item['ayat_start'] }}-{{ $item['ayat_end'] }}</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="font-medium {{ $item['percent'] >= 100 ? 'text-primary dark:text-primary' : 'text-slate-600 dark:text-white/60' }}">{{ $item['completed_ayat'] }}/{{ $item['total_ayat'] }} ayat</span>
                        <span class="text-[10px] w-8 text-right {{ $item['percent'] >= 100 ? 'text-primary' : 'text-slate-400' }}">{{ $item['percent'] }}%</span>
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- History table --}}
    @if ($records->count() > 0)
        @php $grouped = $records->groupBy(fn($r) => \Carbon\Carbon::parse($r->recorded_date)->format('d M Y')); @endphp
        @foreach ($grouped as $date => $dayRecords)
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">{{ $date }}</h2>
                    <span class="text-xs text-slate-400 dark:text-white/30">{{ $dayRecords->count() }}x hafalan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-white/5">
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Surah</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40">Ayat</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40">Jenis</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40">Grade</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayRecords as $i => $rec)
                                <tr class="border-b border-slate-50 last:border-0 dark:border-white/5">
                                    <td class="px-4 py-2.5 text-slate-400 dark:text-white/30">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2.5 font-medium text-slate-700 dark:text-white/80">{{ $rec->quranMaster->surah_name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-center text-slate-600 dark:text-white/70">{{ $rec->ayat_start }}-{{ $rec->ayat_end }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="inline-flex items-center rounded-md {{ $rec->activity_type === 'ZIADAH' ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400' }} px-2 py-0.5 text-xs font-medium">
                                            {{ $rec->activity_type === 'ZIADAH' ? 'Ziadah' : 'Murajaah' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @php $p = \App\Models\TahfidzRecord::scoreToPredikat($rec->score); @endphp
                                        <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-xs font-bold">{{ $rec->score }} ({{ $p }})</span>
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
            <p class="text-sm text-slate-400 dark:text-white/30">Belum ada riwayat hafalan untuk siswa ini.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const chartUrl = '{{ route("guru.quran-hafalan.chart", $student->id) }}';
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
                { label: 'Ziadah (Ayat Baru)', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#10b981' },
                { label: 'Murajaah (Ulangan)', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#3b82f6' }
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
                    displayColors: true,
                    callbacks: {
                        title: function(items) {
                            var idx = items[0].dataIndex;
                            var date = chartData.labels[idx] || '';
                            var count = chartData.counts ? chartData.counts[idx] : 0;
                            return date + '  (' + count + 'x hafalan)';
                        },
                        afterBody: function(items) {
                            var idx = items[0].dataIndex;
                            var lines = [];
                            if (chartData.avg_scores && chartData.avg_scores[idx] !== null) {
                                lines.push('Skor rata-rata: ' + chartData.avg_scores[idx]);
                            }
                            if (chartData.surahs && chartData.surahs[idx] && chartData.surahs[idx].length > 0) {
                                lines.push('Surah: ' + chartData.surahs[idx].join(', '));
                            }
                            return lines;
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
    document.getElementById('statSurah').textContent = d.stats.surahs;
    document.getElementById('statZiadah').textContent = d.stats.ziadah;
    document.getElementById('statMurajaah').textContent = d.stats.murajaah;
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
            lineChart.data.datasets[0].data = d.ayat_ziadah;
            lineChart.data.datasets[1].data = d.ayat_murajaah;
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
            lineChart.data.datasets[0].data = d.ayat_ziadah;
            lineChart.data.datasets[1].data = d.ayat_murajaah;
            lineChart.update();
            updateStats(d);
        });
}

document.addEventListener('DOMContentLoaded', function() { initChart(); loadChart('ta_init'); });

document.querySelectorAll('.target-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var detail = this.nextElementSibling;
        var chevron = this.querySelector('.target-chevron');
        if (detail.classList.contains('hidden')) {
            detail.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            detail.classList.add('hidden');
            chevron.style.transform = '';
        }
    });
});
</script>
@endpush
