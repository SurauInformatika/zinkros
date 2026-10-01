@extends('layouts.app')

@section('title', 'Kelola Sekolah')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Kelola Sekolah</h1>
</div>

@if (session('status'))
    <div class="mb-4 rounded-xl bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-4 py-3 text-sm text-primary dark:text-primary">
        {{ session('status') }}
    </div>
@endif

<form method="GET" action="{{ route('platform.schools.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama sekolah..."
        class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
    <select name="status" class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
        <option value="">Semua status</option>
        @foreach ($statuses as $value)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
        Filter
    </button>
</form>

<div class="overflow-hidden rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
<div class="overflow-x-auto">    <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm whitespace-nowrap">
        <thead class="bg-slate-50 dark:bg-white/5 text-left text-xs uppercase text-slate-500 dark:text-white/40">
            <tr>
                <th class="px-5 py-3 font-semibold">Nama</th>
                <th class="px-5 py-3 font-semibold">Paket</th>
                <th class="px-5 py-3 font-semibold">Status</th>
                <th class="px-5 py-3 font-semibold">Pengguna</th>
                <th class="px-5 py-3 font-semibold">Siswa</th>
                <th class="px-5 py-3 font-semibold">Trial s/d</th>
                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            @forelse ($schools as $school)
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                    <td class="px-5 py-3">
                        <a href="{{ route('platform.schools.show', $school) }}" class="font-medium text-primary dark:text-primary hover:underline">{{ $school->name }}</a>
                        <p class="text-xs text-slate-400 dark:text-white/40">{{ $school->slug }}</p>
                    </td>
                    <td class="px-5 py-3">{{ $school->planLabel() }} <span class="text-xs text-slate-400 dark:text-white/40">({{ $school->plan }})</span></td>
                    <td class="px-5 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs
                            {{ $school->status === 'active' ? 'bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary' : '' }}
                            {{ $school->status === 'trial' ? 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : '' }}
                            {{ $school->status === 'suspended' ? 'bg-rose-100 dark:bg-red-500/10 text-rose-700 dark:text-red-400' : '' }}
                            {{ $school->status === 'expired' ? 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white/50' : '' }}">
                            {{ $school->status }}
                        </span>
                        @if ($school->isSuspended() && $school->suspended_at)
                            <p class="text-[11px] text-red-500/70 dark:text-red-400/50 mt-0.5">sejak {{ $school->suspended_at->format('d M Y') }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $school->users_count }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $school->students_count }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $school->trial_ends_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('platform.schools.show', $school) }}"
                                class="rounded-lg border border-slate-300 dark:border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-white/70 hover:bg-slate-50 dark:hover:bg-white/5">
                                Detail
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-500 dark:text-white/40">Tidak ada sekolah ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

<div class="mt-4">
    {{ $schools->links() }}
</div>
@endsection
