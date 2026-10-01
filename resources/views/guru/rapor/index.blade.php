@extends('layouts.app')

@section('title', 'Rapor — ' . ($classRoom?->class_name ?? 'Wali Kelas'))

@section('content')
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Rapor Siswa</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Rekap nilai semua mata pelajaran siswa {{ $classRoom?->class_name ?? '' }}.</p>
        </div>
        @if ($waliClasses->count() > 1)
        <select
            onchange="window.location = this.value"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            @foreach ($waliClasses as $class)
            <option value="{{ route('guru.rapor.index', ['class_id' => $class->id]) }}" @selected($classRoom && $classRoom->id === $class->id)>{{ $class->class_name }}</option>
            @endforeach
        </select>
        @endif
    </div>
</div>

@if ($classRoom)
<form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
    <input type="hidden" name="class_id" value="{{ $classRoom->id }}">
    <input type="search" name="q" value="{{ $q }}"
        placeholder="Cari nama siswa..."
        class="w-full sm:w-64 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white placeholder:text-slate-400">
    <select name="sort" onchange="this.form.submit()"
        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
        <option value="name" @selected($sort === 'name')>Urutkan Nama</option>
        <option value="avg" @selected($sort === 'avg')>Rata-rata Terendah</option>
    </select>
    <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">Cari</button>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse ($cards as $card)
    @php $student = $card['student']; @endphp
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden flex flex-col">
        {{-- Header siswa --}}
        <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary text-white text-sm font-bold shrink-0">
                {{ strtoupper(substr($student->name, 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="font-semibold truncate">{{ $student->name }}</p>
                <p class="text-xs text-slate-500 dark:text-white/40">NIS {{ $student->nis ?? '-' }} &middot; NISN {{ $student->nisn ?? '-' }}</p>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="px-4 py-3 grid grid-cols-3 gap-2 text-center border-b border-slate-100 dark:border-white/5">
            <div class="rounded-lg bg-slate-50 dark:bg-white/5 px-2 py-1.5">
                <p class="text-xs text-slate-500 dark:text-white/40">Mapel Dinilai</p>
                <p class="text-sm font-bold {{ $card['dinilai'] === $card['total_subjects'] ? 'text-primary dark:text-primary' : 'text-amber-600 dark:text-amber-400' }}">
                    {{ $card['dinilai'] }}/{{ $card['total_subjects'] }}
                </p>
            </div>
            <div class="rounded-lg bg-slate-50 dark:bg-white/5 px-2 py-1.5">
                <p class="text-xs text-slate-500 dark:text-white/40">Rata-rata</p>
                @if ($card['avg'] !== null)
                <p class="text-sm font-bold {{ $card['avg'] >= 80 ? 'text-primary dark:text-primary' : ($card['avg'] >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">{{ $card['avg'] }}</p>
                @else
                <p class="text-sm font-bold text-slate-300 dark:text-white/20">—</p>
                @endif
            </div>
            <div class="rounded-lg {{ $card['below_kkm'] > 0 ? 'bg-red-50 dark:bg-red-500/10' : 'bg-slate-50 dark:bg-white/5' }} px-2 py-1.5">
                <p class="text-xs text-slate-500 dark:text-white/40">Di Bawah KKM</p>
                <p class="text-sm font-bold {{ $card['below_kkm'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-300 dark:text-white/20' }}">{{ $card['below_kkm'] > 0 ? $card['below_kkm'] : '—' }}</p>
            </div>
        </div>

        {{-- Detail mapel (accordion) --}}
        <button type="button" data-rapor-toggle
            class="w-full flex items-center justify-between px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-white/40 hover:text-slate-700 dark:hover:text-white/70 transition-colors">
            <span>Detail Nilai Mapel</span>
            <svg data-rapor-chevron class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div data-rapor-detail class="hidden px-4 pb-3 space-y-1 max-h-60 overflow-y-auto">
            @foreach ($card['rows'] as $row)
            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50 dark:border-white/5 last:border-0">
                <span class="text-slate-600 dark:text-white/70 truncate">
                    {{ $row['subject']->name }}
                    <span class="text-[10px] text-slate-400 dark:text-white/30">(KKM {{ $row['kkm'] }})</span>
                </span>
                @if ($row['has_data'])
                <span class="inline-flex items-center gap-1.5">
                    <span class="font-bold {{ $row['below_kkm'] ? 'text-red-600 dark:text-red-400' : 'text-primary dark:text-primary' }}">{{ $row['final'] }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 dark:text-white/30">{{ \App\Http\Controllers\Guru\RaporController::predikat($row['final']) }}</span>
                </span>
                @else
                <span class="text-slate-300 dark:text-white/15">—</span>
                @endif
            </div>
            @endforeach
        </div>

        @if ($card['tahfidz'])
        <div class="px-4 py-2.5 border-t border-slate-100 dark:border-white/5 text-xs">
            <span class="text-slate-500 dark:text-white/40">Tahfidz: </span>
            <span class="font-semibold text-purple-600 dark:text-purple-400">{{ $card['tahfidz']['ayat'] }} ayat</span>
            <span class="text-slate-400 dark:text-white/30"> &middot; {{ $card['tahfidz']['surahs'] }} surah &middot; {{ $card['tahfidz']['total'] }} setoran</span>
        </div>
        @endif

        <div class="p-4 mt-auto flex items-center justify-between gap-3 border-t border-slate-100 dark:border-white/5">
            <span class="text-[10px] text-slate-400 dark:text-white/25">
                @if ($card['dinilai'] < $card['total_subjects'])
                {{ $card['total_subjects'] - $card['dinilai'] }} mapel belum dinilai — rapor belum siap cetak
                @elseif ($card['below_kkm'] > 0)
                {{ $card['below_kkm'] }} mapel perlu remedial
                @else
                Semua mapel sudah dinilai
                @endif
            </span>
            <a href="{{ route('guru.rapor.student', $student->id) }}"
                class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1.5 text-xs font-medium transition-colors">
                Lihat Rapor
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-full rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-12 text-center">
        <p class="text-sm text-slate-400 dark:text-white/30">
            {{ $q !== '' ? 'Tidak ada siswa yang cocok dengan pencarian "' . $q . '".' : 'Belum ada siswa pada kelas ini.' }}
        </p>
    </div>
    @endforelse
</div>

<p class="mt-4 text-xs text-slate-400 dark:text-white/30">
    Angka mapel = nilai akhir berbobot dari seluruh tipe penilaian (termasuk cap remedial). Daftar mapel mengikuti plotting kelas {{ $classRoom->class_name }}.
</p>
@else
<div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-12 text-center">
    <p class="text-sm text-slate-400 dark:text-white/30">Anda belum menjadi wali kelas. Hubungi administrasi sekolah untuk penugasan wali kelas.</p>
</div>
@endif
@endsection

@push('scripts')
<script>
(function() {
    document.querySelectorAll('[data-rapor-toggle]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var detail = btn.nextElementSibling;
            var chevron = btn.querySelector('[data-rapor-chevron]');
            var open = !detail.classList.contains('hidden');
            detail.classList.toggle('hidden', open);
            if (chevron) chevron.style.transform = open ? '' : 'rotate(180deg)';
        });
    });
})();
</script>
@endpush