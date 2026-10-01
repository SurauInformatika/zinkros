@extends('layouts.app')

@section('title', 'Alur Tujuan Pembelajaran')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Alur Tujuan Pembelajaran</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola ATP per mata pelajaran.</p>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tipe</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                </tr>
            </thead>
            <tbody>
@forelse ($subjects as $subject)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3 font-medium">{{ $subject->name }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-white/60">{{ $subject->type }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs text-slate-400 dark:text-white/30">Belum ada data</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada mata pelajaran.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
