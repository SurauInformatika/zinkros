@extends('layouts.app')

@section('title', 'Rekap Absensi Mapel')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Rekap Absensi Mapel</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Daftar sesi absensi per mata pelajaran</p>
    </div>

    <form method="GET" action="{{ route('guru.absensi-mapel.rekap') }}" class="rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-[#141414]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Kelas</label>
                <select name="class_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                    <option value="">Semua Kelas</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" {{ ($classId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</label>
                <select name="subject_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                    <option value="">Semua Mapel</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}" {{ ($subjectId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Filter</button>
                <a href="{{ route('guru.absensi-mapel.rekap') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-white/60 dark:hover:bg-white/5">Reset</a>
            </div>
        </div>
    </form>

    @if ($sessions->count() > 0)
        <div class="space-y-3">
            @foreach ($sessions as $s)
                <a href="{{ route('guru.absensi-mapel.rekap-detail', ['date' => $s['date'], 'subject_id' => $s['subject']->id, 'class_id' => $s['class_id']]) }}"
                   class="group block rounded-xl border border-slate-200 bg-white p-4 transition hover:border-primary/30 hover:shadow-md dark:border-white/10 dark:bg-[#141414] dark:hover:border-primary/30">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-white/40">
                                <span class="font-medium text-slate-700 dark:text-white/80">{{ \Carbon\Carbon::parse($s['date'])->translatedFormat('d M Y') }}</span>
                                <span>·</span>
                                <span class="truncate">{{ $s['subject']->name }}</span>
                                <span>·</span>
                                <span class="font-medium text-slate-700 dark:text-white/80">{{ $s['class_name'] }}</span>
                            </div>
                            @if ($s['topic'])
                                <p class="mt-1 text-xs text-slate-400 dark:text-white/30 truncate">Topik: {{ $s['topic'] }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 font-medium text-primary dark:bg-primary/100/10 dark:text-primary">Hadir {{ $s['hadir'] }}</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Sakit {{ $s['sakit'] }}</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Izin {{ $s['izin'] }}</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Alpa {{ $s['alpa'] }}</span>
                            <span class="ml-1 font-bold {{ $s['persentase'] >= 80 ? 'text-primary dark:text-primary' : ($s['persentase'] >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">{{ $s['persentase'] }}%</span>
                            <span class="ml-2 text-slate-400 group-hover:text-primary dark:text-white/20 dark:group-hover:text-primary">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-[#141414]">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-white/80">{{ $chartTitle }}</h2>
            <div class="relative" style="height:280px">
                <canvas id="rekapChart"></canvas>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center dark:border-white/10 dark:bg-[#141414]">
            <p class="text-sm text-slate-400 dark:text-white/30">Belum ada data absensi di rentang waktu ini.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if ($sessions->count() > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('rekapChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    { label: 'Hadir', data: {!! json_encode($chartHadir) !!}, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6 },
                    { label: 'Sakit', data: {!! json_encode($chartSakit) !!}, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6 },
                    { label: 'Izin', data: {!! json_encode($chartIzin) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6 },
                    { label: 'Alpa', data: {!! json_encode($chartAlpa) !!}, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointHoverRadius: 6 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } }
                }
            }
        });
    </script>
@endif
@endpush
