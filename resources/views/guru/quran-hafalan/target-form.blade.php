@extends('layouts.app')

@section('title', isset($target) ? 'Edit Target Hafalan' : 'Buat Target Hafalan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-hafalan.input', $student->id) }}" class="rounded-lg border border-slate-200 dark:border-white/10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white/60 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ isset($target) ? 'Edit Target Hafalan' : 'Buat Target Hafalan' }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $student->name }} — {{ $student->classRoom?->class_name ?? '-' }}</p>
        </div>
    </div>

    @if (session('success'))
    <div class="rounded-lg bg-primary/10 dark:bg-primary/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
    <div class="rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Detail Target</h2>
                </div>
                <form method="POST"
                    action="{{ isset($target) ? route('guru.quran-hafalan.target-update', [$student->id, $target->id]) : route('guru.quran-hafalan.target-store') }}"
                    class="p-4 space-y-4">
                    @csrf
                    @if (isset($target))
                        @method('PUT')
                    @endif
                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Judul Target <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required value="{{ old('title', $target->title ?? '') }}"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20"
                                placeholder="cth: Target Hafalan Juz 29-30">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Deadline <span class="text-red-500">*</span></label>
                            <input type="date" name="target_date" required value="{{ old('target_date', isset($target) ? $target->target_date->format('Y-m-d') : now()->addMonths(6)->format('Y-m-d')) }}"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Catatan</label>
                        <textarea name="notes" rows="2" maxlength="1000"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20"
                            placeholder="Opsional...">{{ old('notes', $target->notes ?? '') }}</textarea>
                    </div>

                    @if (isset($target))
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $target->is_active) ? 'checked' : '' }}
                            class="rounded border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20">
                        <label for="isActive" class="text-sm text-slate-600 dark:text-white/60">Target aktif</label>
                    </div>
                    @endif

                    <div class="border-t border-slate-200 dark:border-white/10 pt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-white/80">Daftar Surah</h3>
                            <div class="flex items-center gap-2">
                                <select id="juzSelect" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                                    <option value="">Pilih Juz...</option>
                                    @for ($juz = 1; $juz <= 30; $juz++)
                                        <option value="{{ $juz }}">Juz {{ $juz }}</option>
                                    @endfor
                                </select>
                                <button type="button" onclick="addSurahRow()"
                                    class="rounded-lg border border-primary/20 dark:border-primary/20 px-3 py-1.5 text-xs font-medium text-primary dark:text-primary hover:bg-primary/10 dark:hover:bg-primary/10 transition-colors">
                                    + Tambah Surah
                                </button>
                            </div>
                        </div>
                        <div id="surahRows" class="space-y-2">
                            @if (isset($target) && $target->items->isNotEmpty())
                                @foreach ($target->items as $idx => $item)
                                <div class="surah-row grid grid-cols-1 sm:grid-cols-[1fr_8rem_8rem_auto] gap-2 items-center">
                                    <select name="surah_ids[]" class="surah-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                                        <option value="">— Pilih Surah —</option>
                                        @foreach ($surahs as $surah)
                                            <option value="{{ $surah->id }}" data-total="{{ $surah->total_ayats }}" {{ $item->quran_master_id === $surah->id ? 'selected' : '' }}>
                                                {{ $surah->surah_number }}. {{ $surah->surah_name }} ({{ $surah->total_ayats }} ayat)
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-slate-400 dark:text-white/30">dari</span>
                                        <input type="number" name="ayat_starts[]" value="{{ $item->ayat_start }}" min="1" placeholder="1"
                                            class="ayat-start w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-center dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-slate-400 dark:text-white/30">s/d</span>
                                        <input type="number" name="ayat_ends[]" value="{{ $item->ayat_end }}" min="1" placeholder="{{ $item->quranMaster->total_ayats }}"
                                            class="ayat-end w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-center dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                                    </div>
                                    <button type="button" onclick="this.closest('.surah-row').remove()"
                                        class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-dark transition-colors">
                            {{ isset($target) ? 'Simpan Perubahan' : 'Simpan Target' }}
                        </button>
                        <a href="{{ route('guru.quran-hafalan.input', $student->id) }}"
                            class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Kembali</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-4">
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 mb-2">Info Target</h3>
                <p class="text-sm text-slate-600 dark:text-white/70">
                    Atur target hafalan untuk siswa. Pilih surah dan rentang ayat yang harus dihafal. Progres dihitung dari record Ziadah terakhir.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    window.surahOptions = [
        @foreach ($surahs as $surah)
        { id: '{{ $surah->id }}', number: {{ $surah->surah_number }}, label: '{{ $surah->surah_number }}. {{ $surah->surah_name }} ({{ $surah->total_ayats }} ayat)', total: {{ $surah->total_ayats }} },
        @endforeach
    ];

    @php
        $juzStarts = [
            1 => [1, 1], 2 => [2, 142], 3 => [2, 253], 4 => [3, 93], 5 => [4, 24],
            6 => [4, 148], 7 => [5, 82], 8 => [6, 111], 9 => [7, 88], 10 => [8, 41],
            11 => [9, 93], 12 => [11, 6], 13 => [12, 53], 14 => [15, 1], 15 => [17, 1],
            16 => [18, 75], 17 => [21, 1], 18 => [23, 1], 19 => [25, 21], 20 => [27, 56],
            21 => [29, 46], 22 => [33, 31], 23 => [36, 28], 24 => [39, 32], 25 => [41, 47],
            26 => [46, 1], 27 => [51, 31], 28 => [58, 1], 29 => [67, 1], 30 => [78, 1],
        ];
        $surahTotal = $surahs->pluck('total_ayats', 'surah_number');
        $surahUuid = $surahs->pluck('id', 'surah_number');
        $juzMap = [];
        for ($j = 1; $j <= 30; $j++) {
            [$startSurah, $startAyah] = $juzStarts[$j];
            [$nextSurah, $nextAyah] = $juzStarts[$j + 1] ?? [115, 1];
            $endSurah = $nextSurah;
            $endAyah = $nextAyah - 1;
            if ($endAyah === 0) {
                $endSurah = $nextSurah - 1;
                $endAyah = $surahTotal[$endSurah] ?? 0;
            }
            $items = [];
            if ($startSurah === $endSurah) {
                $items[] = [$startSurah, $startAyah, $endAyah];
            } else {
                $items[] = [$startSurah, $startAyah, $surahTotal[$startSurah]];
                for ($s = $startSurah + 1; $s < $endSurah; $s++) {
                    $items[] = [$s, 1, $surahTotal[$s]];
                }
                $items[] = [$endSurah, 1, $endAyah];
            }
            $juzMap[$j] = array_map(
                fn ($it) => ['id' => $surahUuid[$it[0]], 'start' => $it[1], 'end' => $it[2]],
                $items
            );
        }
    @endphp
    window.juzMap = {!! json_encode($juzMap) !!};

    function addSurahRow(selectedId, start, end) {
        var container = document.getElementById('surahRows');
        var row = document.createElement('div');
        row.className = 'surah-row grid grid-cols-1 sm:grid-cols-[1fr_8rem_8rem_auto] gap-2 items-center';

        var opts = '<option value="">— Pilih Surah —</option>';
        window.surahOptions.forEach(function(s) {
            var sel = s.id === selectedId ? ' selected' : '';
            opts += '<option value="' + s.id + '" data-total="' + s.total + '"' + sel + '>' + s.label + '</option>';
        });

        row.innerHTML =
            '<select name="surah_ids[]" class="surah-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">' + opts + '</select>' +
            '<div class="flex items-center gap-1"><span class="text-xs text-slate-400 dark:text-white/30">dari</span>' +
            '<input type="number" name="ayat_starts[]" value="' + (start || 1) + '" min="1" placeholder="1" class="ayat-start w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-center dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white"></div>' +
            '<div class="flex items-center gap-1"><span class="text-xs text-slate-400 dark:text-white/30">s/d</span>' +
            '<input type="number" name="ayat_ends[]" value="' + (end || '') + '" min="1" placeholder="ayat" class="ayat-end w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-center dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white"></div>' +
            '<button type="button" onclick="this.closest(\'.surah-row\').remove()" class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">' +
            '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';

        container.appendChild(row);
        wireRow(row);
    }

    function wireRow(row) {
        var sel = row.querySelector('.surah-select');
        var start = row.querySelector('.ayat-start');
        var end = row.querySelector('.ayat-end');
        sel.addEventListener('change', function() {
            var opt = sel.options[sel.selectedIndex];
            if (opt && opt.dataset.total) {
                if (!end.value || parseInt(end.value) > parseInt(opt.dataset.total)) {
                    end.value = opt.dataset.total;
                }
            }
        });
        start.addEventListener('input', function() {
            var opt = sel.options[sel.selectedIndex];
            if (opt && opt.dataset.total) {
                if (parseInt(this.value) > parseInt(opt.dataset.total)) this.value = opt.dataset.total;
            }
        });
        end.addEventListener('input', function() {
            var opt = sel.options[sel.selectedIndex];
            if (opt && opt.dataset.total) {
                if (parseInt(this.value) > parseInt(opt.dataset.total)) this.value = opt.dataset.total;
            }
        });
    }

    window.addSurahRow = function() { addSurahRow(null, 1, ''); };

    document.querySelectorAll('.surah-row').forEach(wireRow);

    var juzSelect = document.getElementById('juzSelect');
    if (juzSelect) {
        juzSelect.addEventListener('change', function() {
            var juz = this.value;
            if (!juz) return;
            var items = window.juzMap[juz] || [];
            var container = document.getElementById('surahRows');
            container.innerHTML = '';
            items.forEach(function(item) {
                addSurahRow(item.id, item.start, item.end);
            });
        });
    }
})();
</script>
@endpush
