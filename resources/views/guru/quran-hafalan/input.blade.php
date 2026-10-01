@extends('layouts.app')

@section('title', 'Input Hafalan — ' . $student->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-hafalan.students') }}" class="rounded-lg border border-slate-200 dark:border-white/10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white/60 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Input Hafalan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $student->name }} — {{ $student->classRoom?->class_name ?? '-' }}</p>
        </div>
    </div>

    @if (session('success'))
    <div class="rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Form Input Hafalan</h2>
                </div>
                <form id="hafalanForm" method="POST" action="{{ route('guru.quran-hafalan.store') }}" class="p-4 space-y-4">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="status" id="statusInput" value="">

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="recorded_date" id="recordedDate" value="{{ $date }}" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 sm:w-64">
                    </div>

                    <div class="flex items-center gap-2 p-3 rounded-lg bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/10">
                        <button type="button" id="modeHafalan" onclick="setMode('hafalan')" class="px-4 py-2 text-xs font-medium rounded-lg bg-primary/100 text-white transition-colors">Input Hafalan</button>
                        <button type="button" id="modeSakit" onclick="setMode('sakit')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Sakit</button>
                        <button type="button" id="modeIzin" onclick="setMode('izin')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Izin</button>
                    </div>

                    <div id="hafalanFields">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Surah <span class="text-red-500">*</span></label>
                                <select name="quran_master_id" id="surahSelect"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                                    <option value="">— Pilih Surah —</option>
                                    @foreach ($surahs as $surah)
                                        <option value="{{ $surah->id }}" data-total="{{ $surah->total_ayats }}" {{ $lastRecord && $lastRecord->quran_master_id === $surah->id ? 'selected' : '' }}>
                                            {{ $surah->surah_number }}. {{ $surah->surah_name }} ({{ $surah->total_ayats }} ayat)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Ayat Mulai <span class="text-red-500">*</span></label>
                                    <input type="number" name="ayat_start" id="ayatStart" min="1"
                                        value="{{ $lastRecord ? $lastRecord->ayat_end + 1 : '' }}"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Ayat Akhir <span class="text-red-500">*</span></label>
                                    <input type="number" name="ayat_end" id="ayatEnd" min="1"
                                        value="{{ $lastRecord ? $lastRecord->ayat_end + 5 : '' }}"
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
                                        <option value="ZIADAH">Ziadah (Hafal Baru)</option>
                                        <option value="MURAJAAH">Murajaah (Mengulang)</option>
                                    </select>
                                    <span id="jenisBadge" class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary px-2 py-0.5 text-xs font-bold ml-2 whitespace-nowrap">Ziadah</span>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Skor (0-100) <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="score" id="scoreInput" min="0" max="100"
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
                            placeholder="Opsional..."></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" id="submitBtn"
                            class="rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-dark transition-colors">
                            Simpan Hafalan
                        </button>
                        <a href="{{ route('guru.quran-hafalan.students') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Kembali</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div id="targetBox" class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-medium text-slate-500 dark:text-white/40">Target Hafalan</h3>
                    <a href="{{ route('guru.quran-hafalan.target-create', $student->id) }}" class="text-xs text-primary hover:text-primary dark:text-primary">+ Tambah</a>
                </div>
                @forelse ($targets as $target)
                <div class="rounded-lg border border-slate-200 dark:border-white/10 p-3 mb-2">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-sm font-medium text-slate-700 dark:text-white/80 truncate">{{ $target['title'] ?? 'Target' }}</span>
                        <span class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-bold {{ $target['status'] === 'completed' ? 'text-primary dark:text-primary' : ($target['status'] === 'overdue' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">
                                {{ $target['percent'] }}%
                            </span>
                            <span class="flex items-center gap-1">
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
                            </span>
                        </span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
                        <div class="h-full rounded-full {{ $target['status'] === 'completed' ? 'bg-primary/100' : ($target['status'] === 'overdue' ? 'bg-red-500' : 'bg-primary/100/80') }}"
                            style="width: {{ min(100, $target['percent']) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-[10px] text-slate-400 dark:text-white/30">{{ $target['completed_ayat'] }}/{{ $target['total_ayat'] }} ayat</span>
                        <span class="text-[10px] text-slate-400 dark:text-white/30">{{ $target['days_remaining'] >= 0 ? $target['days_remaining'] . ' hari lagi' : 'Lewat deadline' }}</span>
                    </div>

                    <button type="button" class="mt-2 text-xs text-slate-500 dark:text-white/40 hover:text-slate-700 dark:hover:text-white/60 flex items-center gap-1 target-toggle">
                        <svg class="h-3 w-3 transition-transform target-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        Detail surah
                    </button>
                    <div class="target-detail hidden mt-2 space-y-1.5">
                        @foreach ($target['items'] as $item)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-white/40 truncate">
                                {{ $item['surah_number'] }}. {{ $item['surah_name'] }}
                                <span class="text-slate-400 dark:text-white/30">{{ $item['ayat_start'] }}-{{ $item['ayat_end'] }}</span>
                            </span>
                            <span class="font-medium {{ $item['percent'] >= 100 ? 'text-primary dark:text-primary' : 'text-slate-600 dark:text-white/60' }}">{{ $item['completed_ayat'] }}/{{ $item['total_ayat'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                    <p class="text-xs text-slate-400 dark:text-white/30">Belum ada target. Klik "+ Tambah" untuk membuat target hafalan.</p>
                @endforelse
            </div>

            <div id="lastRecordBox" class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-4" style="{{ $lastRecord ? '' : 'display:none' }}">
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 mb-2">Hafalan Terakhir</h3>
                <div class="space-y-1.5" id="lastRecordContent">
                    @if ($lastRecord)
                    <div class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $lastRecord->quranMaster->surah_name }} {{ $lastRecord->ayat_start }}-{{ $lastRecord->ayat_end }}</div>
                    <div class="text-xs text-slate-400 dark:text-white/30">{{ \Carbon\Carbon::parse($lastRecord->recorded_date)->format('d M Y') }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center rounded-md {{ $lastRecord->activity_type === 'ZIADAH' ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400' }} px-2 py-0.5 text-xs font-medium">{{ $lastRecord->activity_type === 'ZIADAH' ? 'Ziadah' : 'Murajaah' }}</span>
                        @php $p = \App\Models\TahfidzRecord::scoreToPredikat($lastRecord->score); @endphp
                        <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-xs font-bold">{{ $lastRecord->score }} ({{ $p }})</span>
                    </div>
                    @if ($lastRecord->notes)
                        <p class="text-xs text-slate-400 dark:text-white/30 mt-1">{{ $lastRecord->notes }}</p>
                    @endif
                    @endif
                </div>
            </div>

            <div id="existingBox" class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-500/20 dark:bg-amber-500/5 p-4" style="{{ $existingToday->isNotEmpty() ? '' : 'display:none' }}">
                <h3 class="text-xs font-medium text-amber-700 dark:text-amber-400 mb-2">Sudah Diinput di Tanggal Ini</h3>
                <div id="existingContent">
                    @foreach ($existingToday as $rec)
                    @php $p = \App\Models\TahfidzRecord::scoreToPredikat($rec->score); @endphp
                    <div class="text-sm text-amber-700 dark:text-amber-400">
                        {{ $rec->quranMaster->surah_name }} {{ $rec->ayat_start }}-{{ $rec->ayat_end }}
                        <span class="font-bold ml-1">{{ $rec->score }} ({{ $p }})</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
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
    var recordedDate = document.getElementById('recordedDate');
    var statusInput = document.getElementById('statusInput');
    var hafalanFields = document.getElementById('hafalanFields');
    var absentFields = document.getElementById('absentFields');
    var absentLabel = document.getElementById('absentLabel');
    var pastRecords = {!! $pastRecords->toJson() !!};
    var allRecords = {!! $allRecords->toJson() !!};
    var surahMap = {};
    surahSelect.querySelectorAll('option[data-total]').forEach(function(opt) {
        surahMap[opt.value] = { name: opt.textContent.replace(/^\d+\.\s*/, ''), total: parseInt(opt.dataset.total) };
    });

    var currentMode = 'hafalan';

    window.setMode = function(mode) {
        currentMode = mode;
        var btnH = document.getElementById('modeHafalan');
        var btnS = document.getElementById('modeSakit');
        var btnI = document.getElementById('modeIzin');
        var activeCls = 'bg-primary/100 text-white';
        var inactiveCls = 'border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5';

        btnH.className = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ' + (mode === 'hafalan' ? activeCls : inactiveCls);
        btnS.className = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ' + (mode === 'sakit' ? 'bg-amber-500 text-white' : inactiveCls);
        btnI.className = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ' + (mode === 'izin' ? 'bg-blue-500 text-white' : inactiveCls);

        if (mode === 'hafalan') {
            statusInput.value = '';
            hafalanFields.style.display = '';
            absentFields.style.display = 'none';
        } else {
            statusInput.value = mode === 'sakit' ? 'SAKIT' : 'IZIN';
            hafalanFields.style.display = 'none';
            absentFields.style.display = '';
            absentLabel.textContent = mode === 'sakit'
                ? 'Siswa ditandai sebagai Sakit. Tidak perlu input hafalan.'
                : 'Siswa ditandai sebagai Izin. Tidak perlu input hafalan.';
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
        return pastRecords.some(function(r) {
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
                         : 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary') +
                ' px-2 py-0.5 text-xs font-bold ml-2';
        }
    }

    function getPredikat(score) {
        if (score >= 90) return { label: 'A', cls: 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' };
        if (score >= 75) return { label: 'B', cls: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400' };
        if (score >= 60) return { label: 'C', cls: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' };
        return { label: 'D', cls: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400' };
    }

    function formatDate(d) {
        var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
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

    function updateSidebar() {
        var selectedDate = recordedDate.value;
        var dateRecords = allRecords.filter(function(r) { return r.recorded_date === selectedDate; });

        var existingBox = document.getElementById('existingBox');
        var existingContent = document.getElementById('existingContent');
        if (dateRecords.length > 0) {
            existingBox.style.display = '';
            existingContent.innerHTML = dateRecords.map(function(r) {
                var s = surahMap[r.quran_master_id] || { name: '-' };
                var st = r.status || 'HADIR';
                var stCls = st === 'SAKIT' ? 'text-amber-600' : st === 'IZIN' ? 'text-blue-600' : '';
                if (st !== 'HADIR') {
                    return '<div class="text-sm text-amber-700 dark:text-amber-400">' + s.name + ' — <span class="font-bold ' + stCls + '">' + st + '</span></div>';
                }
                var p = getPredikatFromScore(r.score);
                return '<div class="text-sm text-amber-700 dark:text-amber-400">' + s.name + ' ' + r.ayat_start + '-' + r.ayat_end +
                    ' <span class="font-bold ml-1">' + r.score + ' (' + p + ')</span></div>';
            }).join('');
        } else {
            existingBox.style.display = 'none';
        }

        var lastBox = document.getElementById('lastRecordBox');
        var lastContent = document.getElementById('lastRecordContent');
        var last = allRecords.find(function(r) { return r.recorded_date < selectedDate; });
        if (last) {
            lastBox.style.display = '';
            var s = surahMap[last.quran_master_id] || { name: '-' };
            var jt = last.activity_type === 'ZIADAH' ? 'Ziadah' : 'Murajaah';
            var jtCls = last.activity_type === 'ZIADAH' ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400';
            var p = getPredikatFromScore(last.score);
            var pCls = getPredikatCls(last.score);
            lastContent.innerHTML =
                '<div class="text-sm font-medium text-slate-700 dark:text-white/80">' + s.name + ' ' + last.ayat_start + '-' + last.ayat_end + '</div>' +
                '<div class="text-xs text-slate-400 dark:text-white/30">' + formatDate(new Date(last.recorded_date + 'T00:00:00')) + '</div>' +
                '<div class="flex items-center gap-2 mt-1">' +
                    '<span class="inline-flex items-center rounded-md ' + jtCls + ' px-2 py-0.5 text-xs font-medium">' + jt + '</span>' +
                    '<span class="inline-flex items-center rounded-md ' + pCls + ' px-2 py-0.5 text-xs font-bold">' + last.score + ' (' + p + ')</span>' +
                '</div>' +
                (last.notes ? '<p class="text-xs text-slate-400 dark:text-white/30 mt-1">' + last.notes + '</p>' : '');

            surahSelect.value = last.quran_master_id;
            surahSelect.dispatchEvent(new Event('change'));
            var nextStart = last.ayat_end + 1;
            var maxAyat = surahMap[last.quran_master_id] ? surahMap[last.quran_master_id].total : 286;
            if (nextStart > maxAyat) nextStart = 1;
            ayatStart.value = nextStart;
            var nextEnd = nextStart + 5;
            if (nextEnd > maxAyat) nextEnd = maxAyat;
            ayatEnd.value = nextEnd;
        } else {
            lastBox.style.display = 'none';
            surahSelect.value = '';
            surahSelect.dispatchEvent(new Event('change'));
            ayatStart.value = '';
            ayatEnd.value = '';
        }

        updateJenis();
    }

    function getPredikatFromScore(score) {
        if (score >= 90) return 'A';
        if (score >= 75) return 'B';
        if (score >= 60) return 'C';
        return 'D';
    }

    function getPredikatCls(score) {
        var p = getPredikatFromScore(score);
        if (p === 'A') return 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary';
        if (p === 'B') return 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400';
        if (p === 'C') return 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400';
        return 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400';
    }

    scoreInput.addEventListener('input', updatePredikat);
    updatePredikat();

    surahSelect.addEventListener('change', function() {
        clamp(ayatStart);
        clamp(ayatEnd);
        updateJenis();
    });

    ayatStart.addEventListener('input', function() { clamp(this); updateJenis(); });
    ayatEnd.addEventListener('input', function() { clamp(this); updateJenis(); });

    recordedDate.addEventListener('change', function() { updateSidebar(); });

    if (surahSelect.value) {
        clamp(ayatStart);
        clamp(ayatEnd);
        updateJenis();
    }

    document.getElementById('hafalanForm').addEventListener('submit', function() {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });

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
})();
</script>
@endpush
