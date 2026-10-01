@extends('layouts.app')

@section('title', 'Edit KALDIK: ' . $kaldik->name)

@php
    $statusColors = [
        'draft'     => 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white/50',
        'pending'   => 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
        'final'     => 'bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary',
        'archived'  => 'bg-slate-100 dark:bg-white/10 text-slate-400 dark:text-white/30',
    ];
    $statusLabels = [
        'draft' => 'Draf', 'pending' => 'Menunggu Approval', 'final' => 'Final', 'archived' => 'Arsip',
    ];
    $isDraft = $kaldik->isDraft();
@endphp

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight">{{ $kaldik->name }}</h1>
            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $statusColors[$kaldik->status] ?? '' }}">{{ $statusLabels[$kaldik->status] ?? $kaldik->status }}</span>
        </div>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
            {{ $kaldik->academicYear->name ?? '' }} &middot; {{ $kaldik->semesterLabel() }} &middot;
            {{ $kaldik->start_date->format('d M Y') }} — {{ $kaldik->end_date->format('d M Y') }}
            @if ($kaldik->version > 1)
                &middot; Revisi v{{ $kaldik->version }}
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if ($isDraft)
            <form method="POST" action="{{ route('wakasek.kaldik.submit', $kaldik) }}" onsubmit="return confirm('Submit untuk approval Kepsek?')">
                @csrf
                <button type="submit" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 text-sm font-medium transition-colors">Submit untuk Approval</button>
            </form>
        @elseif ($kaldik->isFinal())
            <form method="POST" action="{{ route('wakasek.kaldik.revise', $kaldik) }}" onsubmit="return confirm('Buat revisi draft dari versi FINAL ini? Versi lama akan diarsipkan setelah revisi disetujui Kepsek.')">
                @csrf
                <button type="submit" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 text-sm font-medium transition-colors">Buat Revisi</button>
            </form>
        @endif
        <a href="{{ route('wakasek.kaldik.index') }}" class="rounded-lg border border-slate-200 dark:border-white/10 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">Kembali</a>
    </div>
</div>

@if (session('success'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
    <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (!$isDraft)
<div class="mb-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 p-4 text-sm text-amber-700 dark:text-amber-400">
    Kalender berstatus <strong>{{ $statusLabels[$kaldik->status] ?? $kaldik->status }}</strong> bersifat read-only.
    @if ($kaldik->isFinal())
        Gunakan tombol <strong>Buat Revisi</strong> di kanan atas untuk membuat versi baru yang bisa diedit dan diajukan approval ulang.
    @elseif ($kaldik->isPending())
        Revisi diperlukan setelah Kepsek menolak (status kembali Draf), atau tunggu hasil approval.
    @endif
</div>
@endif

<div class="space-y-6">

    {{-- Section 1: Info Dasar --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Dasar</h2>
        <form method="POST" action="{{ route('wakasek.kaldik.update', $kaldik) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $kaldik->name) }}" required {{ !$isDraft ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Cakupan</label>
                    <div class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 px-4 py-2.5 text-sm text-slate-500 dark:text-white/40">
                        {{ $kaldik->semesterLabel() }} <span class="text-xs">(satu tahun ajaran)</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Sumber</label>
                    <select name="source" required {{ !$isDraft ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                        <option value="custom" {{ $kaldik->source === 'custom' ? 'selected' : '' }}>Custom</option>
                        <option value="kemenag" {{ $kaldik->source === 'kemenag' ? 'selected' : '' }}>Kemenag</option>
                        <option value="dindik" {{ $kaldik->source === 'dindik' ? 'selected' : '' }}>Dinas Pendidikan</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Mulai Semester 1</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $kaldik->start_date->format('Y-m-d')) }}" required {{ !$isDraft ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Mulai Semester 2 <span class="text-xs text-slate-400">(khusus, opsional)</span></label>
                    <input type="date" name="semester_2_start_date" value="{{ old('semester_2_start_date', $kaldik->semester_2_start_date?->format('Y-m-d')) }}" {{ !$isDraft ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                    @error('semester_2_start_date')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Akhir Semester 2</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $kaldik->end_date->format('Y-m-d')) }}" required {{ !$isDraft ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-400 dark:text-white/40">Kosongkan "Mulai Semester 2" bila batas semester otomatis. Jika diisi, pemisahan blok Ganjil/Genap di dokumen memakai tanggal ini, bukan tebakan bulan.</p>

            @if ($isDraft)
            <div id="rangePreview" class="mt-4 rounded-xl border border-primary/20 dark:border-primary/20 bg-primary/10/60 dark:bg-primary/100/5 px-4 py-3 text-sm text-slate-600 dark:text-white/60">
                <span class="font-medium text-primary dark:text-primary">Semester 1:</span> <span id="previewS1">—</span>
                <span class="mx-2 text-slate-300 dark:text-white/20">|</span>
                <span class="font-medium text-primary dark:text-primary">Semester 2:</span> <span id="previewS2">—</span>
                <span id="previewNote" class="mt-1 block text-xs text-slate-400 dark:text-white/30"></span>
            </div>
            <script>
                const MONTHS2 = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                function fmt2(d) { if (!d) return '—'; const x = new Date(d + 'T00:00:00'); return x.getDate() + ' ' + MONTHS2[x.getMonth()] + ' ' + x.getFullYear(); }
                function updateRange2() {
                    const s1 = document.querySelector('[name=start_date]')?.value;
                    const s2 = document.querySelector('[name=semester_2_start_date]')?.value;
                    const end = document.querySelector('[name=end_date]')?.value;
                    const pS1 = document.getElementById('previewS1');
                    const pS2 = document.getElementById('previewS2');
                    const pN = document.getElementById('previewNote');
                    if (!s1 || !end) { pS1.textContent = '—'; pS2.textContent = '—'; pN.textContent = ''; return; }
                    if (s2) {
                        const day = new Date(s2 + 'T00:00:00'); day.setDate(day.getDate() - 1);
                        const s1End = day.getFullYear() + '-' + String(day.getMonth() + 1).padStart(2, '0') + '-' + String(day.getDate()).padStart(2, '0');
                        pS1.textContent = fmt2(s1) + ' – ' + fmt2(s1End);
                        pS2.textContent = fmt2(s2) + ' – ' + fmt2(end);
                        pN.textContent = 'Pemisahan blok Ganjil/Genap di dokumen memakai tanggal mulai Semester 2 ini.';
                    } else {
                        pS1.textContent = fmt2(s1) + ' – ' + fmt2(end);
                        pS2.textContent = 'otomatis';
                        pN.textContent = 'Kosong = blok Ganjil/Genap dibagi per bulan (Jul–Des & Jan–Jun).';
                    }
                }
                ['start_date', 'semester_2_start_date', 'end_date'].forEach(function (id) {
                    const el = document.querySelector('[name=' + id + ']');
                    if (el) { el.addEventListener('change', updateRange2); el.addEventListener('input', updateRange2); }
                });
                updateRange2();
            </script>
            @endif
            @if ($isDraft)
                <button type="submit" class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">Simpan Info Dasar</button>
            @endif
        </form>
    </div>

    {{-- Section 2: Struktur & Durasi JP per Jenjang (merged) --}}
    @php
        $emptyDayMap = [];
        foreach ($weekDays as $d) {
            $emptyDayMap[$d] = 0;
        }
    @endphp
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
        <h2 class="text-lg font-semibold mb-1">Struktur &amp; Durasi JP per Jenjang</h2>
        <p class="text-xs text-slate-400 dark:text-white/30 mb-1">
            Jumlah JP per hari (0 = libur mingguan) &amp; durasi menit per JP — boleh berbeda antar jenjang.
        </p>
        <p class="text-xs text-primary/80 dark:text-primary/70 mb-4">
            Contoh: Kelas 1–3 &rarr; Senin 7jp, Selasa 7jp, Rabu 7jp, Kamis 7jp, Jumat 5jp, Sabtu 0, Ahad 0 &mdash; mnt/JP 35. Kelas 4–6 bisa beda, mis. mnt/JP 40.
        </p>
        <form method="POST" action="{{ route('wakasek.kaldik.level-structure', $kaldik) }}" class="space-y-3" id="levelForm">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/5">
                            <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40 w-40">Jenjang</th>
                            @foreach ($weekDays as $day)
                            <th class="px-2 py-2 text-center text-slate-500 dark:text-white/40 capitalize min-w-[68px]">{{ \Illuminate\Support\Str::title($day) }}</th>
                            @endforeach
                            <th class="px-3 py-2 text-center text-slate-500 dark:text-white/40">mnt/JP</th>
                            <th class="px-3 py-2 text-center text-slate-500 dark:text-white/40">JP/mgg</th>
                            <th class="px-3 py-2 text-center text-slate-500 dark:text-white/40">mnt/mgg</th>
                            @if ($isDraft)
                            <th class="px-3 py-2 w-16"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="levelRows">
                        @forelse ($kaldik->levelStructures as $ls)
                        <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 level-row">
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="structures[{{ $loop->index }}][grade_level_start]" value="{{ $ls->grade_level_start }}" min="1" max="12" placeholder="Dari" {{ !$isDraft ? 'disabled' : '' }}
                                        class="lvl-start w-16 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                                    <span class="text-slate-400 dark:text-white/30 text-xs">s/d</span>
                                    <input type="number" name="structures[{{ $loop->index }}][grade_level_end]" value="{{ $ls->grade_level_end }}" min="1" max="12" placeholder="Sampai" {{ !$isDraft ? 'disabled' : '' }}
                                        class="lvl-end w-16 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                                </div>
                            </td>
                            @foreach ($weekDays as $day)
                            <td class="px-2 py-2">
                                <input type="number" name="structures[{{ $loop->parent->index }}][jp_per_day][{{ $day }}]" value="{{ $ls->jpForDay($day) }}" min="0" max="20" {{ !$isDraft ? 'disabled' : '' }} data-day="{{ $day }}"
                                    class="jp-day w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                            </td>
                            @endforeach
                            @php $jpWk = 0; foreach ($weekDays as $d) { $jpWk += $ls->jpForDay($d); } @endphp
                            <td class="px-3 py-2">
                                <input type="number" name="structures[{{ $loop->index }}][jp_duration_minutes]" value="{{ $ls->jp_duration_minutes }}" min="10" max="120" {{ !$isDraft ? 'disabled' : '' }}
                                    class="mnt-jp w-20 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                            </td>
                            <td class="px-3 py-2 text-center font-semibold jp-week">{{ $jpWk }}</td>
                            <td class="px-3 py-2 text-center text-slate-600 dark:text-white/60 mnt-week">{{ $jpWk * $ls->jp_duration_minutes }}</td>
                            @if ($isDraft)
                            <td class="px-3 py-2">
                                <button type="button" onclick="this.closest('.level-row').remove(); refreshTotals();" class="ml-2 text-red-400 hover:text-red-600 text-xs">Hapus</button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 level-row">
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="structures[0][grade_level_start]" value="1" min="1" max="12" placeholder="Dari" {{ !$isDraft ? 'disabled' : '' }}
                                        class="lvl-start w-16 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                                    <span class="text-slate-400 dark:text-white/30 text-xs">s/d</span>
                                    <input type="number" name="structures[0][grade_level_end]" value="3" min="1" max="12" placeholder="Sampai" {{ !$isDraft ? 'disabled' : '' }}
                                        class="lvl-end w-16 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                                </div>
                            </td>
                            @foreach ($weekDays as $day)
                            <td class="px-2 py-2">
                                <input type="number" name="structures[0][jp_per_day][{{ $day }}]" value="{{ $emptyDayMap[$day] }}" min="0" max="20" {{ !$isDraft ? 'disabled' : '' }} data-day="{{ $day }}"
                                    class="jp-day w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                            </td>
                            @endforeach
                            <td class="px-3 py-2">
                                <input type="number" name="structures[0][jp_duration_minutes]" value="35" min="10" max="120" {{ !$isDraft ? 'disabled' : '' }}
                                    class="mnt-jp w-20 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition disabled:opacity-50">
                            </td>
                            <td class="px-3 py-2 text-center font-semibold jp-week">0</td>
                            <td class="px-3 py-2 text-center text-slate-600 dark:text-white/60 mnt-week">0</td>
                            @if ($isDraft)
                            <td class="px-3 py-2">
                                <button type="button" onclick="this.closest('.level-row').remove(); refreshTotals();" class="ml-2 text-red-400 hover:text-red-600 text-xs">Hapus</button>
                            </td>
                            @endif
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-xl border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 px-4 py-2.5">
                <span class="text-xs text-slate-500 dark:text-white/40">Total JP/minggu (semua jenjang): <b class="text-sm text-primary dark:text-primary" id="totalJpWeek">0</b></span>
                <span class="text-xs text-slate-500 dark:text-white/40">Total mnt/minggu: <b class="text-sm text-primary dark:text-primary" id="totalMntWeek">0</b></span>
            </div>
            <p class="mt-3 text-xs text-slate-400 dark:text-white/30">
                Keterangan: JP = Jam Pelajaran. Angka 0 pada hari tertentu = libur mingguan (mis. Sabtu/Ahad).
                JP/minggu &amp; mnt/minggu terhitung otomatis dan diperbarui langsung saat Anda mengetik, sebelum menekan Simpan.
            </p>
            @if ($isDraft)
                <div class="flex items-center gap-3">
                    <button type="button" onclick="addLevelRow()" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">+ Tambah Jenjang</button>
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">Simpan Struktur &amp; Durasi</button>
                </div>
            @endif
        </form>
    </div>

    {{-- Section 4: Libur --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
        <h2 class="text-lg font-semibold mb-4">Libur</h2>

        @if ($kaldik->holidays->count() > 0)
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5">
                        <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40">Tanggal</th>
                        <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40">Nama</th>
                        <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40">Tipe</th>
                        @if ($isDraft)
                        <th class="px-3 py-2 w-16"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kaldik->holidays->sortBy('date') as $holiday)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                        <td class="px-3 py-2 text-xs">{{ $holiday->date->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-sm">{{ $holiday->name }}</td>
                        <td class="px-3 py-2"><span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-white/60 capitalize">{{ $holiday->type }}</span></td>
                        @if ($isDraft)
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('wakasek.kaldik.holiday.destroy', [$kaldik, $holiday]) }}" onsubmit="return confirm('Hapus libur ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if ($isDraft)
        <form method="POST" action="{{ route('wakasek.kaldik.details', $kaldik) }}" class="space-y-3" id="holidayForm" onsubmit="pruneEmptyHolidayRows()">
            @csrf
            <div id="holidayRows" class="space-y-3"></div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="addHolidayRow()" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">+ Tambah Libur</button>
                <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-1.5 text-sm font-medium transition-colors">Simpan Libur</button>
            </div>
        </form>
        @endif
    </div>

    {{-- Section 5: Kegiatan Khusus --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
        <h2 class="text-lg font-semibold mb-4">Kegiatan Khusus</h2>

        @if ($kaldik->classOverrides->count() > 0)
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5">
                        <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40">Judul</th>
                        <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40">Periode</th>
                        <th class="px-3 py-2 text-left text-slate-500 dark:text-white/40">Level</th>
                        @if ($isDraft)
                        <th class="px-3 py-2 w-16"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kaldik->classOverrides as $co)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                        <td class="px-3 py-2 text-sm">{{ $co->title }}</td>
                        <td class="px-3 py-2 text-xs text-slate-500 dark:text-white/40">{{ $co->start_date->format('d M') }} — {{ $co->end_date->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-xs text-slate-500 dark:text-white/40">{{ $co->grade_level ? 'Kelas ' . $co->grade_level : 'Semua' }}</td>
                        @if ($isDraft)
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('wakasek.kaldik.override.destroy', [$kaldik, $co]) }}" onsubmit="return confirm('Hapus override ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if ($isDraft)
        <form method="POST" action="{{ route('wakasek.kaldik.details', $kaldik) }}" class="space-y-3" id="overrideForm" onsubmit="pruneEmptyOverrideRows()">
            @csrf
            <div id="overrideRows" class="space-y-3"></div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="addOverrideRow()" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">+ Tambah Kegiatan</button>
                <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-1.5 text-sm font-medium transition-colors">Simpan Kegiatan</button>
            </div>
        </form>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function fmtNum(n) {
    return n.toLocaleString('id-ID');
}

var holidayTypeOptions = '';
@foreach (['nasional', 'daerah', 'sekolah', 'rutin'] as $type)
    holidayTypeOptions += '<option value="{{ $type }}">{{ ucfirst($type) }}</option>';
@endforeach

var gradeOptions = '<option value="">Semua Level</option>';
@for ($i = 1; $i <= 12; $i++)
    gradeOptions += '<option value="{{ $i }}">Kelas {{ $i }}</option>';
@endfor

function refreshLevelRow(tr) {
    var jp = 0;
    tr.querySelectorAll('.jp-day').forEach(function (i) { jp += parseInt(i.value || '0', 10); });
    tr.querySelectorAll('.jp-week').forEach(function (s) { s.textContent = fmtNum(jp); });
    var dur = parseInt((tr.querySelector('.mnt-jp') || {}).value || '0', 10);
    tr.querySelectorAll('.mnt-week').forEach(function (s) { s.textContent = fmtNum(jp * dur); });
    refreshTotals();
}

function refreshTotals() {
    var jp = 0, mnt = 0;
    document.querySelectorAll('#levelRows .level-row').forEach(function (tr) {
        var j = 0;
        tr.querySelectorAll('.jp-day').forEach(function (i) { j += parseInt(i.value || '0', 10); });
        jp += j;
        var dur = parseInt((tr.querySelector('.mnt-jp') || {}).value || '0', 10);
        mnt += j * dur;
    });
    var jpEl = document.getElementById('totalJpWeek');
    var mntEl = document.getElementById('totalMntWeek');
    if (jpEl) jpEl.textContent = fmtNum(jp);
    if (mntEl) mntEl.textContent = fmtNum(mnt);
}

function addLevelRow() {
    var tbody = document.getElementById('levelRows');
    var rows = tbody.querySelectorAll('.level-row');
    var idx = rows.length;
    var tr = document.createElement('tr');
    tr.className = 'border-b border-slate-50 dark:border-white/5 last:border-0 level-row';
    var daysHtml = '';
    @foreach ($weekDays as $day)
        daysHtml += '<td class="px-2 py-2">' +
            '<input type="number" name="structures[' + idx + '][jp_per_day][{{ $day }}]" value="0" min="0" max="20" data-day="{{ $day }}" ' +
            'class="jp-day w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
            '</td>';
    @endforeach
    tr.innerHTML =
        '<td class="px-3 py-2">' +
        '<div class="flex items-center gap-2">' +
        '<input type="number" name="structures[' + idx + '][grade_level_start]" value="1" min="1" max="12" placeholder="Dari" class="lvl-start w-16 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
        '<span class="text-slate-400 dark:text-white/30 text-xs">s/d</span>' +
        '<input type="number" name="structures[' + idx + '][grade_level_end]" value="6" min="1" max="12" placeholder="Sampai" class="lvl-end w-16 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
        '</div></td>' +
        daysHtml +
        '<td class="px-3 py-2">' +
        '<input type="number" name="structures[' + idx + '][jp_duration_minutes]" value="35" min="10" max="120" class="mnt-jp w-20 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1.5 text-sm text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
        '</td>' +
        '<td class="px-3 py-2 text-center font-semibold jp-week">0</td>' +
        '<td class="px-3 py-2 text-center text-slate-600 dark:text-white/60 mnt-week">0</td>' +
        '<td class="px-3 py-2">' +
        '<button type="button" onclick="this.closest(\'.level-row\').remove(); refreshTotals();" class="ml-2 text-red-400 hover:text-red-600 text-xs">Hapus</button>' +
        '</td>';
    tbody.appendChild(tr);
    tr.querySelectorAll('.jp-day').forEach(function (i) { i.addEventListener('input', function () { refreshLevelRow(tr); }); });
    var durInp = tr.querySelector('.mnt-jp');
    if (durInp) durInp.addEventListener('input', function () { refreshLevelRow(tr); });
    refreshLevelRow(tr);
}

function pruneEmptyHolidayRows() {
    document.querySelectorAll('#holidayRows .holiday-row').forEach(function (r) {
        var d = r.querySelector('input[name$="[date]"]').value;
        var n = r.querySelector('input[name$="[name]"]').value;
        if (!d && !n) r.remove();
    });
}

function pruneEmptyOverrideRows() {
    document.querySelectorAll('#overrideRows .override-row').forEach(function (r) {
        var t = r.querySelector('input[name$="[title]"]').value;
        var s = r.querySelector('input[name$="[start_date]"]').value;
        if (!t && !s) r.remove();
    });
}

function addHolidayRow() {
    var box = document.getElementById('holidayRows');
    var idx = box.children.length;
    var div = document.createElement('div');
    div.className = 'holiday-row rounded-lg border border-slate-100 dark:border-white/5 p-4';
    div.innerHTML =
        '<div class="grid grid-cols-1 sm:grid-cols-12 gap-3">' +
        '<div class="sm:col-span-3">' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tanggal</label>' +
        '<input type="date" name="holidays[' + idx + '][date]" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
        '</div>' +
        '<div class="sm:col-span-4">' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Nama</label>' +
        '<input type="text" name="holidays[' + idx + '][name]" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="contoh: Libur Nasional">' +
        '</div>' +
        '<div class="sm:col-span-3">' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tipe</label>' +
        '<select name="holidays[' + idx + '][type]" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' + holidayTypeOptions + '</select>' +
        '</div>' +
        '<div class="sm:col-span-2 flex items-end">' +
        '<button type="button" onclick="this.closest(\'.holiday-row\').remove()" class="text-red-400 hover:text-red-600 text-xs">Hapus</button>' +
        '</div>' +
        '</div>';
    box.appendChild(div);
}

function addOverrideRow() {
    var box = document.getElementById('overrideRows');
    var idx = box.children.length;
    var div = document.createElement('div');
    div.className = 'override-row rounded-lg border border-slate-100 dark:border-white/5 p-4 space-y-3';
    div.innerHTML =
        '<div>' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Judul</label>' +
        '<input type="text" name="overrides[' + idx + '][title]" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="contoh: UTS Semester 1">' +
        '</div>' +
        '<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tanggal Mulai</label>' +
        '<input type="date" name="overrides[' + idx + '][start_date]" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tanggal Selesai</label>' +
        '<input type="date" name="overrides[' + idx + '][end_date]" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
        '</div>' +
        '<div>' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Level (opsional)</label>' +
        '<select name="overrides[' + idx + '][grade_level]" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' + gradeOptions + '</select>' +
        '</div>' +
        '</div>' +
        '<div class="flex items-start justify-between gap-3">' +
        '<div class="flex-1">' +
        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Catatan (opsional)</label>' +
        '<textarea name="overrides[' + idx + '][notes]" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Keterangan tambahan..."></textarea>' +
        '</div>' +
        '<button type="button" onclick="this.closest(\'.override-row\').remove()" class="text-red-400 hover:text-red-600 text-xs mt-6">Hapus</button>' +
        '</div>';
    box.appendChild(div);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#levelRows .level-row').forEach(function (tr) {
        tr.querySelectorAll('.jp-day').forEach(function (i) { i.addEventListener('input', function () { refreshLevelRow(tr); }); });
        var durInp = tr.querySelector('.mnt-jp');
        if (durInp) durInp.addEventListener('input', function () { refreshLevelRow(tr); });
        refreshLevelRow(tr);
    });
});
</script>
@endpush
