@extends('layouts.app')

@section('title', "Rekap Tasmi'")

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Rekap Tasmi'</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Riwayat Tasmi' siswa yang Anda bimbing.</p>
        </div>
        <a href="{{ route('guru.tasmi.students') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Input Tasmi'
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-primary/20 bg-primary/10 p-4 text-sm text-primary">{{ session('success') }}</div>
    @endif

    @if ($records->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center dark:border-white/10 dark:bg-[#141414]">
            <p class="font-medium text-slate-700 dark:text-white/80">Belum ada rekap Tasmi'.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Data Tasmi' siswa yang Anda input akan muncul di halaman ini.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($records as $record)
                @php
                    $pageGroups = $record->items
                        ->sortBy(fn ($item) => [$item->page_number ?? 0, $item->quranMaster?->surah_number ?? 0])
                        ->groupBy(fn ($item) => $item->page_number !== null ? 'page-'.$item->page_number : 'legacy-'.$item->id);
                    $averageScores = collect(['fluency_score', 'tajwid_score', 'fasohah_score'])
                        ->mapWithKeys(fn ($field) => [
                            $field => $pageGroups
                                ->map(fn ($items) => $items->first()->{$field})
                                ->filter(fn ($score) => $score !== null)
                                ->avg(),
                        ]);
                    $hasLegacyScores = $pageGroups->contains(
                        fn ($items) => $items->first()->score !== null
                    );
                @endphp
                <details class="group overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414]">
                    <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 bg-slate-50 px-4 py-3 marker:hidden dark:bg-white/[0.02] [&::-webkit-details-marker]:hidden">
                        <div>
                            <h2 class="font-semibold text-slate-800 dark:text-white/90">{{ $record->student?->name ?? 'Siswa tidak ditemukan' }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-white/40">{{ $record->student?->classRoom?->class_name ?? 'Tanpa Kelas' }} · {{ \Carbon\Carbon::parse($record->recorded_date)->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $record->juz ? 'Juz '.$record->juz : 'Tanpa juz' }}</span>
                            <span class="text-xs text-slate-500 dark:text-white/40 group-open:hidden">Lihat rincian</span>
                            <span class="hidden text-xs text-slate-500 dark:text-white/40 group-open:inline">Tutup rincian</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                        </div>
                    </summary>
                    <div class="border-t border-slate-200 dark:border-white/10">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1040px] text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 text-left text-xs text-slate-500 dark:border-white/5 dark:text-white/40">
                                        <th class="px-4 py-2 font-medium">Halaman / Mode</th>
                                        <th class="px-4 py-2 font-medium">Surah</th>
                                        <th class="px-4 py-2 text-center font-medium">Kelancaran</th>
                                        <th class="px-4 py-2 text-center font-medium">Tajwid</th>
                                        <th class="px-4 py-2 text-center font-medium">Fasohah</th>
                                        <th class="px-4 py-2 text-center font-medium">Rata-rata</th>
                                        <th class="px-4 py-2 text-center font-medium">Grade</th>
                                        @if ($hasLegacyScores)
                                            <th class="px-4 py-2 text-center font-medium">Nilai lama</th>
                                        @endif
                                        <th class="px-4 py-2 font-medium">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pageGroups as $items)
                                        @php
                                            $firstItem = $items->first();
                                            $segments = $items->sortBy(fn ($item) => $item->quranMaster?->surah_number ?? 0);
                                            $itemScores = collect([$firstItem->fluency_score, $firstItem->tajwid_score, $firstItem->fasohah_score]);
                                            $itemAverage = $itemScores->every(fn ($score) => $score !== null) ? $itemScores->avg() : null;
                                            $itemGrade = $itemAverage !== null
                                                ? \App\Models\TahfidzRecord::scoreToPredikat((int) round($itemAverage))
                                                : null;
                                        @endphp
                                        <tr class="border-b border-slate-50 last:border-0 dark:border-white/5">
                                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-white/80">
                                                {{ $firstItem->page_number ? 'Hal. '.$firstItem->page_number : 'Per surah' }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-white/60">
                                                @foreach ($segments as $segment)
                                                    <div class="whitespace-nowrap">
                                                        {{ $segment->quranMaster?->surah_number }}. {{ $segment->quranMaster?->surah_name ?? 'Surah tidak ditemukan' }}
                                                        <span class="text-slate-400 dark:text-white/40">({{ $segment->ayat_start }}–{{ $segment->ayat_end }})</span>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex min-w-10 justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-white/10 dark:text-white/80">{{ $firstItem->fluency_score ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex min-w-10 justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-white/10 dark:text-white/80">{{ $firstItem->tajwid_score ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex min-w-10 justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-white/10 dark:text-white/80">{{ $firstItem->fasohah_score ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-white/80">{{ $itemAverage !== null ? number_format($itemAverage, 1) : '—' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($itemGrade)
                                                    <span class="inline-flex min-w-8 justify-center rounded-md px-2 py-1 text-xs font-bold {{ \App\Models\TahfidzRecord::predikatColor($itemGrade) }}">{{ $itemGrade }}</span>
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            @if ($hasLegacyScores)
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex min-w-10 justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-white/10 dark:text-white/80">{{ $firstItem->score ?? '—' }}</span>
                                                </td>
                                            @endif
                                            <td class="px-4 py-3 text-slate-600 dark:text-white/60">{{ $firstItem->notes ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-6 border-t border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.02]">
                            @foreach (['fluency_score' => 'Rata-rata kelancaran', 'tajwid_score' => 'Rata-rata tajwid', 'fasohah_score' => 'Rata-rata fasohah'] as $field => $label)
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-white/40">{{ $label }}</p>
                                    <p class="mt-0.5 text-lg font-bold text-slate-800 dark:text-white/90">{{ $averageScores[$field] !== null ? number_format($averageScores[$field], 1) : '—' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        <div>{{ $records->links() }}</div>
    @endif
</div>
@endsection
