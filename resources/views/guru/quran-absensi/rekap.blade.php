@extends('layouts.app')

@section('title', 'Rekap Kehadiran Al-Quran')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Rekap Kehadiran Al-Quran</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Daftar sesi kehadiran hafalan Al-Quran</p>
    </div>

    <form method="GET" action="{{ route('guru.quran-absensi.rekap') }}" class="rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-[#141414]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="flex-1 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark sm:flex-none">Filter</button>
                <a href="{{ route('guru.quran-absensi.rekap') }}" class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-center text-sm text-slate-600 hover:bg-slate-50 sm:flex-none dark:border-white/10 dark:text-white/60 dark:hover:bg-white/5">Reset</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-[#141414]">
        <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80" id="chartTitle">Grafik Kehadiran — {{ $totalStudents }} Siswa</h2>
            <div>
                <label for="chartFilter" class="sr-only">Rentang grafik</label>
                <select id="chartFilter" onchange="loadChart(this.value)"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                    <option value="pekan" @selected(request('chart') === 'pekan')>1 Pekan</option>
                    <option value="pekan_lalu" @selected(request('chart') === 'pekan_lalu')>Pekan Lalu</option>
                    <option value="bulan" @selected(request('chart') === 'bulan')>Bulan Ini</option>
                    <option value="bulan_lalu" @selected(request('chart') === 'bulan_lalu')>Bulan Lalu</option>
                    <option value="semester" @selected(request('chart') === 'semester')>Semester Ini</option>
                    <option value="semester_lalu" @selected(request('chart') === 'semester_lalu')>Semester Lalu</option>
                    <option value="ta_init" @selected(request('chart') === 'ta_init')>TA Ini</option>
                    <option value="ta_lalu" @selected(request('chart') === 'ta_lalu')>TA Lalu</option>
                </select>
            </div>
        </div>
        <div class="relative" style="height:280px">
            <canvas id="rekapChart"></canvas>
        </div>
    </div>

    @if ($sessions->count() > 0)
        <div class="space-y-3">
            @foreach ($sessions as $s)
                @if ($s['is_empty'])
                    <div class="block rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[0.02]">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-medium text-slate-500 dark:text-white/40">{{ \Carbon\Carbon::parse($s['date'])->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 dark:bg-white/5 dark:text-white/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                    Tidak ada setoran (Libur / Kegiatan Sekolah)
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('guru.quran-absensi.detail', $s['date']) }}" class="block rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-[#141414] hover:border-primary/30 dark:hover:border-primary/30 transition-all">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-white/40">
                                    <span class="font-medium text-slate-700 dark:text-white/80">{{ \Carbon\Carbon::parse($s['date'])->translatedFormat('d M Y') }}</span>
                                    <span>·</span>
                                    <span>{{ $s['total'] }} siswa</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 font-medium text-primary dark:bg-primary/100/10 dark:text-primary">Hadir {{ $s['hadir'] }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Sakit {{ $s['sakit'] }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Izin {{ $s['izin'] }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Alpa {{ $s['alpa'] }}</span>
                                <span class="font-bold {{ $s['persentase'] >= 80 ? 'text-primary dark:text-primary' : ($s['persentase'] >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">{{ $s['persentase'] }}%</span>
                                <svg class="ml-auto h-4 w-4 shrink-0 text-slate-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center dark:border-white/10 dark:bg-[#141414]">
            <p class="text-sm text-slate-400 dark:text-white/30">Belum ada data kehadiran Al-Quran di rentang waktu ini.</p>
        </div>
    @endif
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        var chartUrl = '{{ route("guru.quran-absensi.chart-data") }}';
        var rekapChart;
        var totalStudents = {{ $totalStudents }};

        function initChart() {
            var isDark = document.documentElement.classList.contains('dark');
            var grid = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
            var txt = isDark ? 'rgba(255,255,255,0.4)' : 'rgba(0,0,0,0.4)';
            Chart.defaults.color = txt;
            Chart.defaults.borderColor = grid;

            rekapChart = new Chart(document.getElementById('rekapChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        { label: 'Hadir', data: {!! json_encode($chartHadir) !!}, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointHoverRadius: 5 },
                        { label: 'Sakit', data: {!! json_encode($chartSakit) !!}, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointHoverRadius: 5 },
                        { label: 'Izin', data: {!! json_encode($chartIzin) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointHoverRadius: 5 },
                        { label: 'Alpa', data: {!! json_encode($chartAlpa) !!}, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.3, pointRadius: 3, pointHoverRadius: 5 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, font: { size: 11 } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: grid } }
                    }
                }
            });
        }

        function loadChart(filter) {
            fetch(chartUrl + '?filter=' + filter)
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    rekapChart.data.labels = d.labels;
                    rekapChart.data.datasets[0].data = d.hadir;
                    rekapChart.data.datasets[1].data = d.sakit;
                    rekapChart.data.datasets[2].data = d.izin;
                    rekapChart.data.datasets[3].data = d.alpa;
                    rekapChart.update();

                    var total = d.hadir.reduce(function(a, b) { return a + b; }, 0) +
                                d.sakit.reduce(function(a, b) { return a + b; }, 0) +
                                d.izin.reduce(function(a, b) { return a + b; }, 0) +
                                d.alpa.reduce(function(a, b) { return a + b; }, 0);
                    var persentase = total > 0 ? Math.round(d.hadir.reduce(function(a, b) { return a + b; }, 0) / total * 100) : 0;
                    document.getElementById('chartTitle').textContent = 'Grafik Kehadiran — ' + totalStudents + ' Siswa (' + persentase + '% hadir)';
                });
        }

        document.addEventListener('DOMContentLoaded', function() { initChart(); });
    </script>
@endpush
@endsection
