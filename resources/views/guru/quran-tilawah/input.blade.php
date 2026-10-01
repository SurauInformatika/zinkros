@extends('layouts.app')

@section('title', 'Input Tilawah — ' . $student->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-tilawah.students') }}" class="rounded-lg border border-slate-200 dark:border-white/10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white/60 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Input Tilawah</h1>
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
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Form Input Tilawah</h2>
                </div>
                <form method="POST" action="{{ route('guru.quran-tilawah.store') }}" class="p-4 space-y-4">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="status" id="statusInput" value="">

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="recorded_date" value="{{ $date }}" required max="{{ \Carbon\Carbon::today()->toDateString() }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 sm:w-64">
                    </div>

                    <div class="flex items-center gap-2 p-3 rounded-lg bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/10">
                        <button type="button" id="modeHadir" onclick="setMode('hadir')" class="px-4 py-2 text-xs font-medium rounded-lg bg-primary/100 text-white transition-colors">Input Tilawah</button>
                        <button type="button" id="modeSakit" onclick="setMode('sakit')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Sakit</button>
                        <button type="button" id="modeIzin" onclick="setMode('izin')" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">Izin</button>
                    </div>

                    <div id="tilawahFields">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Jenjang Baca <span class="text-red-500">*</span></label>
                                <select name="reading_level_id" id="levelSelect"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20">
                                    <option value="">— Pilih Jilid/Juz —</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}" data-pages="{{ $level->pages }}" {{ $lastRecord && $lastRecord->reading_level_id === $level->id ? 'selected' : '' }}>
                                            {{ $level->label }} ({{ $level->pages }} hal)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Halaman Mulai <span class="text-red-500">*</span></label>
                                <input type="number" name="page_start" id="pageStart" min="1"
                                    value="{{ $lastRecord && $lastRecord->reading_level_id ? $lastRecord->page_end + 1 : '' }}"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Halaman Akhir <span class="text-red-500">*</span></label>
                                <input type="number" name="page_end" id="pageEnd" min="1"
                                    value="{{ $lastRecord && $lastRecord->reading_level_id ? $lastRecord->page_end + 2 : '' }}"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Skor (0-100) <span class="text-red-500">*</span></label>
                            <input type="number" name="score" min="0" max="100"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white focus:border-primary focus:ring-primary/20 text-center sm:w-40"
                                placeholder="0-100">
                        </div>
                    </div>

                    <div id="absentFields" style="display:none">
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/5 border border-amber-200 dark:border-amber-500/20 p-4">
                            <p class="text-sm text-amber-700 dark:text-amber-400" id="absentLabel">Siswa ditandai sebagai Sakit. Tidak perlu input tilawah.</p>
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
                            Simpan Tilawah
                        </button>
                        <a href="{{ route('guru.quran-tilawah.students') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Kembali</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div id="lastRecordBox" class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-4" style="{{ $lastRecord ? '' : 'display:none' }}">
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 mb-2">Terakhir Baca</h3>
                @if ($lastRecord)
                    @if ($lastRecord->reading_level_id)
                        <div class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $lastRecord->readingLevel->label }} hl. {{ $lastRecord->page_start }}-{{ $lastRecord->page_end }}</div>
                        <div class="text-xs text-slate-400 dark:text-white/30 mt-0.5">{{ \Carbon\Carbon::parse($lastRecord->recorded_date)->format('d M Y') }}</div>
                        @if ($lastRecord->score > 0)
                            <div class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary px-2 py-0.5 text-xs font-bold mt-1">{{ $lastRecord->score }}</div>
                        @endif
                    @else
                        <div class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $lastRecord->status ?? 'HADIR' }}</div>
                        <div class="text-xs text-slate-400 dark:text-white/30 mt-0.5">{{ \Carbon\Carbon::parse($lastRecord->recorded_date)->format('d M Y') }}</div>
                    @endif
                    @if ($lastRecord->notes)
                        <p class="text-xs text-slate-400 dark:text-white/30 mt-1">{{ $lastRecord->notes }}</p>
                    @endif
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-medium text-slate-500 dark:text-white/40">Riwayat Tilawah</h3>
                    <a href="{{ route('guru.quran-tilawah.history', $student->id) }}" class="text-xs text-primary hover:text-primary dark:text-primary">Lihat semua</a>
                </div>
                @forelse ($allRecords->take(5) as $rec)
                    <div class="flex items-center justify-between py-1.5 text-xs border-b border-slate-100 last:border-0 dark:border-white/5">
                        <span class="text-slate-500 dark:text-white/40">
                            @if ($rec->reading_level_id)
                                {{ $rec->readingLevel->label }} hl. {{ $rec->page_start }}-{{ $rec->page_end }}
                            @else
                                {{ $rec->status ?? 'HADIR' }}
                            @endif
                        </span>
                        <span class="text-slate-400 dark:text-white/30 whitespace-nowrap">{{ \Carbon\Carbon::parse($rec->recorded_date)->format('d M Y') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 dark:text-white/30">Belum ada riwayat tilawah.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var levelSelect = document.getElementById('levelSelect');
    var pageStart = document.getElementById('pageStart');
    var pageEnd = document.getElementById('pageEnd');
    var statusInput = document.getElementById('statusInput');
    var tilawahFields = document.getElementById('tilawahFields');
    var absentFields = document.getElementById('absentFields');
    var absentLabel = document.getElementById('absentLabel');

    function getMax() {
        var opt = levelSelect.options[levelSelect.selectedIndex];
        return opt && opt.dataset.pages ? parseInt(opt.dataset.pages) : 30;
    }

    window.setMode = function(mode) {
        var btnH = document.getElementById('modeHadir');
        var btnS = document.getElementById('modeSakit');
        var btnI = document.getElementById('modeIzin');
        var activeCls = 'bg-primary/100 text-white';
        var inactiveCls = 'border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-100 dark:hover:bg-white/5';

        btnH.className = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ' + (mode === 'hadir' ? activeCls : inactiveCls);
        btnS.className = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ' + (mode === 'sakit' ? 'bg-amber-500 text-white' : inactiveCls);
        btnI.className = 'px-4 py-2 text-xs font-medium rounded-lg transition-colors ' + (mode === 'izin' ? 'bg-blue-500 text-white' : inactiveCls);

        if (mode === 'hadir') {
            statusInput.value = '';
            tilawahFields.style.display = '';
            absentFields.style.display = 'none';
        } else {
            statusInput.value = mode === 'sakit' ? 'SAKIT' : 'IZIN';
            tilawahFields.style.display = 'none';
            absentFields.style.display = '';
            absentLabel.textContent = mode === 'sakit'
                ? 'Siswa ditandai sebagai Sakit. Tidak perlu input tilawah.'
                : 'Siswa ditandai sebagai Izin. Tidak perlu input tilawah.';
        }
    };

    levelSelect.addEventListener('change', function() {
        var max = getMax();
        var pStart = parseInt(pageStart.value);
        var pEnd = parseInt(pageEnd.value);
        if (pStart && pStart > max) pageStart.value = max;
        if (pEnd && pEnd > max) pageEnd.value = max;
    });

    function clampStart() {
        var max = getMax();
        if (pageStart.value && parseInt(pageStart.value) > max) pageStart.value = max;
    }
    function clampEnd() {
        var max = getMax();
        if (pageEnd.value && parseInt(pageEnd.value) > max) pageEnd.value = max;
    }
    pageStart.addEventListener('input', clampStart);
    pageEnd.addEventListener('input', clampEnd);

    document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });
})();
</script>
@endpush
