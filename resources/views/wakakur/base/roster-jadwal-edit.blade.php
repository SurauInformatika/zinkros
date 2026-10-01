@extends('layouts.app')

@section('title', 'Atur Jadwal - ' . $kelas->class_name)

@section('content')
@php
    $days = App\Models\ClassDaySchedule::DAYS;
    $maxJp = max(array_values($jpByDay) ?: [8]);
    $cellMap = [];
    foreach ($existing as $row) {
        for ($jp = $row->start_jp; $jp <= $row->end_jp; $jp++) {
            $cellMap[$row->day_name][$jp] = $row->subject_id;
        }
    }
    $oldInput = old('schedules', []);
    $hasOld = is_array($oldInput) && count($oldInput) > 0;
@endphp

<div class="mb-6">
    <a href="{{ route('wakasek.base.roster', ['view' => 'jadwal']) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-white/40 hover:text-slate-700 dark:hover:text-white/70 mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Roster
    </a>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Jadwal Mingguan · {{ $kelas->class_name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
                Pilih mapel tiap slot. Mapel berurutan akan otomatis digabung menjadi satu blok (mis. jam 1–2).
            </p>
        </div>
    </div>
</div>

@if ($subjectOptions->count() === 0)
    <div class="rounded-xl bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-500/10 dark:to-yellow-500/10 border border-amber-200 dark:border-amber-500/20 px-4 py-3 text-sm text-amber-700 dark:text-amber-400">
        Belum ada mapel yang diplot untuk kelas ini. Plot mapel + gurunya lewat
        <a href="{{ route('wakasek.base.teachers') }}" class="font-semibold underline">Guru &amp; Tugas</a> dulu, lalu kembali ke sini.
    </div>
@else
    <form method="POST" action="{{ route('wakasek.base.roster.jadwal-store', $kelas) }}" id="jadwal-form">
        @csrf
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm border-separate border-spacing-1">
                    <thead>
                        <tr>
                            <th class="w-16 px-1 py-1 text-left text-xs font-medium text-slate-400 dark:text-white/30">Jam</th>
                            @foreach ($days as $day)
                                @if (($jpByDay[$day] ?? 0) > 0)
                                    <th class="px-1 py-1 text-xs font-semibold text-slate-600 dark:text-white/60 text-center">
                                        {{ ucfirst($day) }}
                                        <span class="block font-normal text-[10px] text-slate-400 dark:text-white/30">{{ $jpByDay[$day] }} JP</span>
                                    </th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for ($jp = 1; $jp <= $maxJp; $jp++)
                            <tr>
                                <td class="px-1 py-1 text-xs font-medium text-slate-500 dark:text-white/40">Jam {{ $jp }}</td>
                                @foreach ($days as $day)
                                    @if (($jpByDay[$day] ?? 0) > 0)
                                        @php
                                            $selected = $hasOld
                                                ? ($oldInput[$day][(string) $jp] ?? '')
                                                : ($cellMap[$day][$jp] ?? '');
                                        @endphp
                                        <td class="p-0.5 min-w-[110px]" data-jadwal-cell data-day="{{ $day }}" data-jp="{{ $jp }}">
                                            <select name="schedules[{{ $day }}][{{ $jp }}]"
                                                class="w-full rounded-md border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-1.5 py-1.5 text-[11px] focus:outline-none focus:ring-2 focus:ring-primary/40">
                                                <option value="">—</option>
                                                @foreach ($subjectOptions as $opt)
                                                    <option value="{{ $opt['id'] }}" data-teacher="{{ $opt['teacher'] }}" @selected((string) $selected === (string) $opt['id'])>
                                                        {{ $opt['name'] }} · {{ $opt['teacher'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary text-white px-5 py-2.5 text-sm font-medium hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Jadwal
                </button>
                <p class="text-xs text-slate-400 dark:text-white/30" data-bentrok-hint>Sel bernomor merah = guru bentrok di jam yang sama.</p>
            </div>
        </div>
    </form>
@endif
@endsection

@section('scripts')
@if ($subjectOptions->count() > 0)
<script>
(function () {
    var cells = Array.prototype.slice.call(document.querySelectorAll('[data-jadwal-cell]'));
    function teacherOf(cell) {
        var sel = cell.querySelector('select');
        if (!sel || !sel.value) return '';
        var opt = sel.selectedOptions[0];
        return opt ? (opt.getAttribute('data-teacher') || '') : '';
    }
    function refresh() {
        var seen = {};
        cells.forEach(function (c) {
            c.classList.remove('ring-2', 'ring-red-500');
            var t = teacherOf(c);
            if (!t) return;
            var key = c.dataset.day + '|' + t;
            (seen[key] = seen[key] || []).push(c);
        });
        Object.keys(seen).forEach(function (key) {
            var group = seen[key];
            for (var i = 0; i < group.length; i++) {
                for (var j = i + 1; j < group.length; j++) {
                    group[i].classList.add('ring-2', 'ring-red-500');
                    group[j].classList.add('ring-2', 'ring-red-500');
                }
            }
        });
    }
    cells.forEach(function (c) {
        var sel = c.querySelector('select');
        if (sel) sel.addEventListener('change', refresh);
    });
    refresh();
})();
</script>
@endif
@endsection