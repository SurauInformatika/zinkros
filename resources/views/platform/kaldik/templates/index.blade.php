@extends('layouts.app')

@section('title', 'Template KALDIK')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Template KALDIK</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola template kalender pendidikan untuk semua sekolah.</p>
    </div>
    <a href="{{ route('platform.kaldik-templates.create') }}" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
        + Buat Template
    </a>
</div>

@if (session('status'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('status') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Sumber</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Dipakai</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $template)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $template->name }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-white/60 capitalize">{{ $template->source }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($template->is_active)
                            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-500 dark:text-white/40">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-slate-500 dark:text-white/40">{{ $template->academic_calendars_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('platform.kaldik-templates.edit', $template) }}" class="text-primary dark:text-primary hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('platform.kaldik-templates.push', $template) }}" onsubmit="return confirm('Push template ini ke semua sekolah aktif?')">
                                @csrf
                                <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Push</button>
                            </form>
                            <form method="POST" action="{{ route('platform.kaldik-templates.destroy', $template) }}" onsubmit="return confirm('Hapus template ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada template.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
