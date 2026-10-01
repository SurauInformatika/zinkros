@extends('layouts.app')

@section('title', 'Input Hafalan')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Input Hafalan</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $classRoom->class_name }} — {{ $subject->name }}</p>
</div>

@if (session('success'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <form id="hafalanForm" method="POST" action="{{ route('guru.tahfidz.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $classRoom->id }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="date" id="hiddenDate" value="{{ $date }}">

        <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center gap-4">
            <label class="text-sm font-medium text-slate-700 dark:text-white/60">Tanggal:</label>
            <input type="date" id="datePicker" value="{{ $date }}"
                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            <span id="dateStatus" class="text-xs text-slate-400 dark:text-white/30"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-8">#</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-40">Surah</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-16">Ayat</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-20">S/D</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-28">Jenis</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-28">Skor</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-36">Catatan</th>
                    </tr>
                </thead>
                <tbody id="studentRows">
                    @foreach ($students as $i => $student)
                    @php $existing = $existingRecords->get($student->id, collect()); @endphp
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]" data-student-id="{{ $student->id }}">
                        <td class="px-4 py-2.5 text-slate-400 dark:text-white/30">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5">
                            <div class="font-medium">{{ $student->name }}</div>
                            @if ($existing->isNotEmpty())
                                @foreach ($existing as $rec)
                                <div class="text-xs text-primary dark:text-primary mt-0.5 existing-info">
                                    {{ $rec->quranMaster->surah_name }} {{ $rec->ayat_start }}-{{ $rec->ayat_end }}
                                    <span class="inline-flex items-center rounded-md {{ $rec->activity_type === 'ZIADAH' ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400' }} px-1.5 py-0.5 text-[10px] font-medium ml-1">{{ $rec->activity_type === 'ZIADAH' ? 'Ziadah' : 'Murajaah' }}</span>
                                    <span class="text-[10px] font-bold ml-1">{{ $rec->score }} ({{ \App\Models\TahfidzRecord::scoreToPredikat($rec->score) }})</span>
                                </div>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-2 py-1.5">
                            <select name="hafalan[{{ $student->id }}][quran_master_id]" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1.5 text-xs focus:border-primary focus:ring-primary/20 dark:text-white/80 surah-select">
                                <option value="">— Pilih —</option>
                                @foreach ($surahs as $surah)
                                <option value="{{ $surah->id }}" data-total="{{ $surah->total_ayats }}" {{ $existing->isNotEmpty() && $existing->first()->quran_master_id === $surah->id ? 'selected' : '' }}>
                                    {{ $surah->surah_number }}. {{ $surah->surah_name }} ({{ $surah->total_ayats }})
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-1.5">
                            <input type="number" name="hafalan[{{ $student->id }}][ayat_start]" min="1" max="286"
                                value="{{ $existing->isNotEmpty() ? $existing->first()->ayat_start : '' }}"
                                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1.5 text-xs text-center focus:border-primary focus:ring-primary/20 dark:text-white/80 ayat-start" placeholder="1">
                        </td>
                        <td class="px-2 py-1.5">
                            <input type="number" name="hafalan[{{ $student->id }}][ayat_end]" min="1" max="286"
                                value="{{ $existing->isNotEmpty() ? $existing->first()->ayat_end : '' }}"
                                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1.5 text-xs text-center focus:border-primary focus:ring-primary/20 dark:text-white/80 ayat-end" placeholder="7">
                        </td>
                        <td class="px-2 py-1.5">
                            <select name="hafalan[{{ $student->id }}][activity_type]" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1.5 text-xs focus:border-primary focus:ring-primary/20 dark:text-white/80 type-select">
                                <option value="ZIADAH" {{ $existing->isNotEmpty() && $existing->first()->activity_type === 'ZIADAH' ? 'selected' : '' }}>Ziadah</option>
                                <option value="MURAJAAH" {{ $existing->isNotEmpty() && $existing->first()->activity_type === 'MURAJAAH' ? 'selected' : '' }}>Murajaah</option>
                            </select>
                        </td>
                        <td class="px-2 py-1.5">
                            <div class="flex items-center gap-1">
                                <input type="number" name="hafalan[{{ $student->id }}][score]" min="0" max="100"
                                    value="{{ $existing->isNotEmpty() ? $existing->first()->score : '' }}"
                                    class="w-16 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1.5 text-xs text-center focus:border-primary focus:ring-primary/20 dark:text-white/80 score-input" placeholder="0-100">
                                <span class="text-[10px] font-bold predikat-badge min-w-[24px] text-center">-</span>
                            </div>
                        </td>
                        <td class="px-2 py-1.5">
                            <input type="text" name="hafalan[{{ $student->id }}][notes]" maxlength="255"
                                value="{{ $existing->isNotEmpty() ? $existing->first()->notes : '' }}"
                                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1.5 text-xs focus:border-primary focus:ring-primary/20 dark:text-white/80 notes-input" placeholder="Opsional">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-between">
            <a href="{{ route('guru.tahfidz.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Kembali</a>
            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-5 py-2.5 text-sm font-medium transition-colors">
                Simpan Hafalan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var datePicker = document.getElementById('datePicker');
    var hiddenDate = document.getElementById('hiddenDate');
    var dateStatus = document.getElementById('dateStatus');
    var classId = '{{ $classRoom->id }}';
    var subjectId = '{{ $subject->id }}';
    var fetchUrl = '{{ route("guru.tahfidz.fetch-by-date") }}';
    var debounceTimer = null;

    function clampAyatInput(input, max) {
        if (input.value && parseInt(input.value) > max) {
            input.value = max;
        }
    }

    function updateAyatMax(row) {
        var surahSelect = row.querySelector('.surah-select');
        var ayatStart = row.querySelector('.ayat-start');
        var ayatEnd = row.querySelector('.ayat-end');
        var opt = surahSelect.options[surahSelect.selectedIndex];
        var total = opt && opt.dataset.total ? parseInt(opt.dataset.total) : 286;
        ayatStart.max = total;
        ayatEnd.max = total;
        clampAyatInput(ayatStart, total);
        clampAyatInput(ayatEnd, total);
    }

    document.querySelectorAll('#studentRows tr[data-student-id]').forEach(function(row) {
        var surahSelect = row.querySelector('.surah-select');
        var ayatStart = row.querySelector('.ayat-start');
        var ayatEnd = row.querySelector('.ayat-end');

        surahSelect.addEventListener('change', function() { updateAyatMax(row); });
        ayatStart.addEventListener('input', function() {
            var opt = surahSelect.options[surahSelect.selectedIndex];
            var total = opt && opt.dataset.total ? parseInt(opt.dataset.total) : 286;
            clampAyatInput(this, total);
        });
        ayatEnd.addEventListener('input', function() {
            var opt = surahSelect.options[surahSelect.selectedIndex];
            var total = opt && opt.dataset.total ? parseInt(opt.dataset.total) : 286;
            clampAyatInput(this, total);
        });
        if (surahSelect.value) updateAyatMax(row);
    });

    datePicker.addEventListener('change', function() {
        var newDate = this.value;
        hiddenDate.value = newDate;
        dateStatus.textContent = 'Memuat data...';
        dateStatus.className = 'text-xs text-amber-500 dark:text-amber-400';

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            fetch(fetchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    class_id: classId,
                    subject_id: subjectId,
                    date: newDate
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var rows = document.querySelectorAll('#studentRows tr[data-student-id]');
                var totalExisting = 0;

                rows.forEach(function(row) {
                    var sid = row.getAttribute('data-student-id');
                    var info = data[sid];
                    if (!info) return;

                    var surahSelect = row.querySelector('.surah-select');
                    var ayatStart = row.querySelector('.ayat-start');
                    var ayatEnd = row.querySelector('.ayat-end');
                    var typeSelect = row.querySelector('.type-select');
                    var scoreInput = row.querySelector('.score-input');
                    var notesInput = row.querySelector('.notes-input');
                    var existingInfo = row.querySelector('.existing-info');

                    function toPredikat(s) {
                        if (s >= 90) return {l:'A', c:'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary'};
                        if (s >= 75) return {l:'B', c:'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'};
                        if (s >= 60) return {l:'C', c:'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'};
                        return {l:'D', c:'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400'};
                    }

                    function updateBadge(input, badge) {
                        var v = parseInt(input.value);
                        if (!input.value || isNaN(v)) { badge.textContent = '-'; badge.className = 'text-[10px] font-bold predikat-badge min-w-[24px] text-center text-slate-400'; return; }
                        var p = toPredikat(v);
                        badge.textContent = p.l;
                        badge.className = 'text-[10px] font-bold predikat-badge min-w-[24px] text-center ' + p.c;
                    }

                    if (info.has_existing) {
                        totalExisting++;
                        var ex = info.existing[0];
                        surahSelect.value = ex.quran_master_id;
                        updateAyatMax(row);
                        ayatStart.value = ex.ayat_start;
                        ayatEnd.value = ex.ayat_end;
                        typeSelect.value = ex.activity_type;
                        scoreInput.value = ex.score;
                        notesInput.value = ex.notes;
                        var badge = row.querySelector('.predikat-badge');
                        if (badge) updateBadge(scoreInput, badge);

                        if (existingInfo) {
                            var p = toPredikat(ex.score);
                            existingInfo.innerHTML = ex.surah_name + ' ' + ex.ayat_start + '-' + ex.ayat_end +
                                ' <span class="inline-flex items-center rounded-md ' + (ex.activity_type === 'ZIADAH' ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400') + ' px-1.5 py-0.5 text-[10px] font-medium ml-1">' + (ex.activity_type === 'ZIADAH' ? 'Ziadah' : 'Murajaah') + '</span> <span class="text-[10px] font-bold ml-1">' + ex.score + ' (' + p.l + ')</span>';
                        }
                    } else {
                        surahSelect.value = '';
                        ayatStart.value = '';
                        ayatEnd.value = '';
                        typeSelect.value = 'ZIADAH';
                        scoreInput.value = '';
                        notesInput.value = '';
                        var badge = row.querySelector('.predikat-badge');
                        if (badge) { badge.textContent = '-'; badge.className = 'text-[10px] font-bold predikat-badge min-w-[24px] text-center text-slate-400'; }

                        if (info.last_surah_id) {
                            surahSelect.value = info.last_surah_id;
                            updateAyatMax(row);
                            if (info.last_ayat_end) {
                                ayatStart.value = parseInt(info.last_ayat_end) + 1;
                            }
                        }

                        if (existingInfo) {
                            existingInfo.innerHTML = '';
                        }
                    }
                });

                if (totalExisting > 0) {
                    dateStatus.textContent = totalExisting + ' siswa sudah diisi';
                    dateStatus.className = 'text-xs text-primary dark:text-primary';
                } else {
                    dateStatus.textContent = 'Belum ada data';
                    dateStatus.className = 'text-xs text-slate-400 dark:text-white/30';
                }
            })
            .catch(function() {
                dateStatus.textContent = 'Gagal memuat data';
                dateStatus.className = 'text-xs text-red-500 dark:text-red-400';
            });
        }, 300);
    });
})();
</script>
@endpush
