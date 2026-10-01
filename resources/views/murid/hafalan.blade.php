@extends('layouts.app')

@section('title', 'Hafalan Al-Quran Saya')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="mb-6">
    <h1 class="text-2xl font-bold">Hafalan Al-Quran Saya</h1>
    <p class="text-sm text-slate-500 dark:text-white/50 mt-1">{{ $student->classRoom?->class_name ?? '-' }} · NIS {{ $student->nis }}</p>
</div>

{{-- Target Hafalan --}}
@if (!empty($targets))
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Target Hafalan Saya</h2>
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
            <span>{{ $target['completed_ayat'] }}/{{ $target['total_ayat'] }} ayat • Batas {{ \Carbon\Carbon::parse($target['target_date'])->format('d M Y') }}</span>
            <span>{{ $target['days_remaining'] >= 0 ? 'Sisa ' . $target['days_remaining'] . ' hari' : 'Lewat ' . abs($target['days_remaining']) . ' hari' }}</span>
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

{{-- Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $summary['ayat'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/50 mt-1">Ayat Terhafal</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-secondary dark:text-secondary">{{ $summary['surahs'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/50 mt-1">Surah</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $summary['ziadah'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/50 mt-1">Ziadah</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['murajaah'] }}</p>
        <p class="text-xs text-slate-500 dark:text-white/50 mt-1">Murajaah</p>
    </div>
</div>

{{-- Chart --}}
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 mb-6">
    <h2 class="font-semibold mb-4">Grafik Hafalan Saya</h2>

    <div class="flex flex-wrap items-center gap-2 mb-5">
        <label for="chartFilter" class="text-xs font-medium text-slate-500 dark:text-white/40">Rentang</label>
        <select id="chartFilter" onchange="loadChart(this.value)"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            <option value="pekan">Pekan Ini</option>
            <option value="bulan">Bulan Ini</option>
            <option value="semester">Semester Ini</option>
            <option value="ta_init" selected>Tahun Ajaran Ini</option>
        </select>
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
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Ziadah</span><span class="font-bold text-primary dark:text-primary" id="statZiadah">0</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Murajaah</span><span class="font-bold text-blue-600 dark:text-blue-400" id="statMurajaah">0</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-white/40">Rata-rata Skor</span><span class="font-bold" id="statAvgScore">0</span></div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat --}}
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    @if ($records->isEmpty())
        <p class="p-6 text-sm text-slate-500 dark:text-white/50">Belum ada catatan hafalan untuk Anda.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-white/50">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Tanggal</th>
                        <th class="text-left px-5 py-3 font-semibold">Surah</th>
                        <th class="text-left px-5 py-3 font-semibold">Ayat</th>
                        <th class="text-left px-5 py-3 font-semibold">Jenis</th>
                        <th class="text-left px-5 py-3 font-semibold">Nilai</th>
                        <th class="text-left px-5 py-3 font-semibold">Guru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($records as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                            <td class="px-5 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($record->recorded_date)->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3">
                                @if ($record->status)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                        {{ $record->status }}
                                    </span>
                                @else
                                    {{ $record->quranMaster?->surah_name ?? '-' }}
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($record->ayat_start || $record->ayat_end)
                                    {{ $record->ayat_start }}{{ $record->ayat_end != $record->ayat_start ? '-' . $record->ayat_end : '' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($record->activity_type)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-400">
                                        {{ $record->activity_type }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($record->score > 0)
                                    <span class="font-semibold">{{ $record->score }}</span>
                                    <span class="ml-1 text-xs text-slate-400">{{ \App\Models\TahfidzRecord::scoreToPredikat($record->score) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $record->teacher?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const chartUrl = '{{ route("murid.hafalan.chart") }}';
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
                    displayColors: true
                }
            },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

function updateStats(d) {
    document.getElementById('statTotal').textContent = d.stats.total;
    document.getElementById('statZiadah').textContent = d.stats.ziadah;
    document.getElementById('statMurajaah').textContent = d.stats.murajaah;
    document.getElementById('statAvgScore').textContent = d.stats.avg_score || 0;
}

function loadChart(filter) {
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
