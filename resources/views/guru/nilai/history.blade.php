@extends('layouts.app')

@section('title', 'Riwayat Nilai')

@section('content')
<div class="mb-6">
    <a href="{{ route('guru.nilai.index') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary">&larr; Kembali</a>
</div>

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Riwayat Nilai</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Semua nilai yang pernah diinput.</p>
</div>

<form method="GET" class="mb-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                <option value="">Semua</option>
                @foreach ($subjects as $s)
                <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tipe</label>
            <select name="grade_type_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                <option value="">Semua</option>
                @foreach ($gradeTypes as $gt)
                <option value="{{ $gt->id }}" {{ request('grade_type_id') == $gt->id ? 'selected' : '' }}>{{ $gt->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">Filter</button>
        </div>
    </div>
</form>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    @if ($grades->isEmpty())
    <div class="p-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada data nilai.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tipe</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Bobot</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Nilai</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grades as $grade)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-2.5">{{ \Carbon\Carbon::parse($grade->date)->format('d M Y') }}</td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-white/60">{{ $grade->gradeType?->name ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-white/40">{{ $grade->gradeType?->weight ?? 0 }}%</td>
                    <td class="px-4 py-2.5 font-medium">
                        @if ($grade->student)
                        <a href="{{ route('guru.nilai.student', $grade->student_id) }}" class="text-slate-700 dark:text-white/80 hover:text-primary dark:hover:text-primary hover:underline transition-colors">{{ $grade->student->name }}</a>
                        @else
                        <span class="text-slate-400 dark:text-white/30">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">{{ $grade->subject->name ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($grade->finalScore() !== null)
                        <span class="inline-flex items-center rounded-md {{ $grade->finalScore() >= 80 ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : ($grade->finalScore() >= 60 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400') }} px-2.5 py-0.5 text-xs font-bold" title="{{ $grade->hasRemedial() ? $grade->score . ' → ' . $grade->remedial_capped . ' (remedial)' : '' }}">{{ $grade->finalScore() }}</span>
                            @if ($grade->hasRemedial())
                            <span class="inline-flex items-center justify-center rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 text-[9px] font-bold px-1 ml-1 align-middle" title="Remedial, di-cap di KKM">R</span>
                            @endif
                        @else
                        <span class="text-xs text-slate-300 dark:text-white/15">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-white/40">{{ $grade->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100 dark:border-white/5">
        {{ $grades->links() }}
    </div>
    @endif
</div>
@endsection
