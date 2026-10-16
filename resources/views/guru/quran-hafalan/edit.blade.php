@extends('layouts.app')

@section('title', 'Edit Riwayat Hafalan — ' . $student->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-hafalan.history', $student->id) }}" class="rounded-lg border border-slate-200 dark:border-white/10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white/60 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Edit Riwayat Hafalan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $student->name }} — {{ $student->classRoom?->class_name ?? '-' }}</p>
        </div>
    </div>

    @if (session('error'))
    <div class="rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
    <div class="rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
        <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Form Edit Hafalan</h2>
        </div>
        <form id="hafalanForm" method="POST" action="{{ route('guru.quran-hafalan.update', $record->id) }}" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" id="statusInput" value="">

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="recorded_date" id="recordedDate" value="{{ \Carbon\Carbon::parse($record->recorded_date)->toDateString() }}" required
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 sm:w-64">
            </div>

            <div class="flex flex-wrap items-center gap-2 p-3 rounded-lg bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/10">
                <button type="button" id="modeHafalan" onclick="setMode('hafalan')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Input Hafalan</button>
                <button type="button" id="modeSakit" onclick="setMode('sakit')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Sakit</button>
                <button type="button" id="modeIzin" onclick="setMode('izin')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Izin</button>
                <button type="button" id="modeAlpa" onclick="setMode('alpa')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Alpa</button>
            </div>

            <div id="hafalanFields">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Surah <span class="text-red-500">*</span></label>
                        <select name="quran_master_id" id="surahSelect"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                            <option value="">— Pilih Surah —</option>
                            @foreach ($surahs as $surah)
                                <option value="{{ $surah->id }}" data-total="{{ $surah->total_ayats }}" {{ $record->quran_master_id === $surah->id ? 'selected' : '' }}>
                                    {{ $surah->surah_number }}. {{ $surah->surah_name }} ({{ $surah->total_ayats }} ayat)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Ayat Mulai <span class="text-red-500">*</span></label>
                            <input type="number" name="ayat_start" id="ayatStart" min="1"
                                value="{{ $record->quran_master_id ? $record->ayat_start : '' }}"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Ayat Akhir <span class="text-red-500">*</span></label>
                            <input type="number" name="ayat_end" id="ayatEnd" min="1"
                                value="{{ $record->quran_master_id ? $record->ayat_end : '' }}"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Jenis <span class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <select name="activity_type" id="activityType"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                                <option value="ZIADAH" {{ $record->activity_type === 'ZIADAH' ? 'selected' : '' }}>Ziadah (Hafal Baru)</option>
                                <option value="MURAJAAH" {{ $record->activity_type === 'MURAJAAH' ? 'selected' : '' }}>Murajaah (Mengulang)</option>
                            </select>
                            <span id="jenisBadge" class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/10 text-primary dark:text-primary px-2 py-0.5 text-xs font-bold ml-2 whitespace-nowrap">Ziadah</span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Skor (0-100) <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="score" id="scoreInput" min="0" max="100"
                                value="{{ $record->quran_master_id ? $record->score : '' }}"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center"
                                placeholder="0-100">
                            <span id="predikatBadge" class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-xs font-bold text-slate-700 dark:text-white/70 min-w-[40px] justify-center">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="absentFields" style="display:none">
                <div class="rounded-lg bg-amber-50 dark:bg-amber-500/5 border border-amber-200 dark:border-amber-500/20 p-4">
                    <p class="text-sm text-amber-700 dark:text-amber-400" id="absentLabel">Siswa ditandai sebagai Sakit. Tidak perlu input hafalan.</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Catatan</label>
                <textarea name="notes" rows="2" maxlength="500"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20"
                    placeholder="Opsional...">{{ $record->notes }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="submitBtn"
                    class="rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-dark transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('guru.quran-hafalan.history', $student->id) }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var surahSelect = document.getElementById('surahSelect');
    var ayatStart = document.getElementById('ayatStart');
    var ayatEnd = document.getElementById('ayatEnd');
    var scoreInput = document.getElementById('scoreInput');
    var predikatBadge = document.getElementById('predikatBadge');
    var activityType = document.getElementById('activityType');
    var jenisBadge = document.getElementById('jenisBadge');
    var statusInput = document.getElementById('statusInput');
    var hafalanFields = document.getElementById('hafalanFields');
    var absentFields = document.getElementById('absentFields');
    var absentLabel = document.getElementById('absentLabel');
    var otherRecords = {!! $otherRecords->toJson() !!};

    var surahMap = {};
    surahSelect.querySelectorAll('option[data-total]').forEach(function(opt) {
        surahMap[opt.value] = { name: opt.textContent.replace(/^\d+\.\s*/, ''), total: parseInt(opt.dataset.total) };
    });

    var initialMode = 'hafalan';
    @if ($record->quran_master_id === null)
        initialMode = '{{ strtolower($record->status ?? "alpa") }}';
    @endif

    window.setMode = function(mode) {
        var btnH = document.getElementById('modeHafalan');
        var btnS = document.getElementById('modeSakit');
        var btnI = document.getElementById('modeIzin');
        var btnA = document.getElementById('modeAlpa');
        var active = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ';
        var inactiveCls = 'border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5';

        btnH.className = active + (mode === 'hafalan' ? 'bg-primary/100 text-white' : inactiveCls);
        btnS.className = active + (mode === 'sakit' ? 'bg-amber-500 text-white' : inactiveCls);
        btnI.className = active + (mode === 'izin' ? 'bg-blue-500 text-white' : inactiveCls);
        btnA.className = active + (mode === 'alpa' ? 'bg-red-500 text-white' : inactiveCls);

        if (mode === 'hafalan') {
            statusInput.value = '';
            hafalanFields.style.display = '';
            absentFields.style.display = 'none';
        } else {
            statusInput.value = mode.toUpperCase();
            hafalanFields.style.display = 'none';
            absentFields.style.display = '';
            var label = mode === 'sakit' ? 'Sakit' : (mode === 'izin' ? 'Izin' : 'Alpa');
            absentLabel.textContent = 'Siswa ditandai sebagai ' + label + '. Tidak perlu input hafalan.';
        }
    };

    function getMax() {
        var opt = surahSelect.options[surahSelect.selectedIndex];
        return opt && opt.dataset.total ? parseInt(opt.dataset.total) : 286;
    }

    function clamp(input) {
        var max = getMax();
        if (input.value && parseInt(input.value) > max) input.value = max;
    }

    function isMurajaah() {
        var masterId = surahSelect.value;
        var start = parseInt(ayatStart.value);
        var end = parseInt(ayatEnd.value);
        if (!masterId || isNaN(start) || isNaN(end)) return false;
        return otherRecords.some(function(r) {
            return r.quran_master_id === masterId && r.ayat_start <= end && r.ayat_end >= start;
        });
    }

    function updateJenis() {
        var murajaah = isMurajaah();
        activityType.value = murajaah ? 'MURAJAAH' : 'ZIADAH';
        if (jenisBadge) {
            jenisBadge.textContent = murajaah ? 'Murajaah' : 'Ziadah';
            jenisBadge.className = 'inline-flex items-center rounded-md ' +
                (murajaah ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'
                         : 'bg-primary/10 dark:bg-primary/10 text-primary dark:text-primary') +
                ' px-2 py-0.5 text-xs font-bold ml-2';
        }
    }

    function getPredikat(score) {
        if (score >= 90) return { label: 'A', cls: 'bg-primary/10 dark:bg-primary/10 text-primary dark:text-primary' };
        if (score >= 75) return { label: 'B', cls: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400' };
        if (score >= 60) return { label: 'C', cls: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' };
        return { label: 'D', cls: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400' };
    }

    function updatePredikat() {
        var val = parseInt(scoreInput.value);
        if (!scoreInput.value || isNaN(val) || val < 0 || val > 100) {
            predikatBadge.textContent = '-';
            predikatBadge.className = 'inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-xs font-bold text-slate-700 dark:text-white/70 min-w-[40px] justify-center';
            return;
        }
        var p = getPredikat(val);
        predikatBadge.textContent = p.label;
        predikatBadge.className = 'inline-flex items-center rounded-md ' + p.cls + ' px-2.5 py-1 text-xs font-bold min-w-[40px] justify-center';
    }

    scoreInput.addEventListener('input', updatePredikat);
    surahSelect.addEventListener('change', function() { clamp(ayatStart); clamp(ayatEnd); updateJenis(); });
    ayatStart.addEventListener('input', function() { clamp(this); updateJenis(); });
    ayatEnd.addEventListener('input', function() { clamp(this); updateJenis(); });

    document.getElementById('hafalanForm').addEventListener('submit', function() {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });

    setMode(initialMode);
    updatePredikat();
    if (surahSelect.value) {
        clamp(ayatStart);
        clamp(ayatEnd);
        if (jenisBadge) {
            var isZiadah = activityType.value === 'ZIADAH';
            jenisBadge.textContent = isZiadah ? 'Ziadah' : 'Murajaah';
            jenisBadge.className = 'inline-flex items-center rounded-md ' +
                (isZiadah ? 'bg-primary/10 dark:bg-primary/10 text-primary dark:text-primary'
                          : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400') +
                ' px-2 py-0.5 text-xs font-bold ml-2';
        }
    }
})();
</script>
@endpush
