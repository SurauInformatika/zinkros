@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@php
    $school = auth()->user()->school;
    $wakasekGroup = \App\Http\Controllers\Admin\PenggunaController::WAKASEK_GROUP;
    $tabs = array_combine($roles, array_map(fn ($r) => $school->roleLabel($r), $roles));
    $activeRoleKey = $viewRole ?? $role;
    $roleLabel = $tabs[$activeRoleKey] ?? 'Pengguna';
@endphp

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Manajemen Pengguna</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola akun guru, staff, orang tua, pimpinan, dan murid dalam satu menu.</p>
    </div>
    @if ($roleLabel !== 'Pengguna')
        <a href="{{ route('admin.pengguna.create', ['role' => $activeRoleKey]) }}"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah {{ $roleLabel }}
        </a>
    @endif
</div>

@include('admin.pengguna._role-tabs', ['activeRole' => $activeRoleKey])

<div class="overflow-hidden rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
<div class="overflow-x-auto">    <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm whitespace-nowrap">
        <thead class="bg-slate-50 dark:bg-white/5 text-left text-xs uppercase text-slate-500 dark:text-white/40">
            <tr>
                <th class="px-5 py-3 font-semibold">Nama</th>
                <th class="px-5 py-3 font-semibold">Jabatan</th>
                <th class="px-5 py-3 font-semibold">Email</th>
                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            @forelse ($users as $user)
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                    <td class="px-5 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">
                        @if ($user->isWakasek() && $user->position)
                            {{ $user->wakasekPositionLabel() }}
                        @else
                            {{ $user->position ? \App\Models\User::WAKASEK_POSITIONS[$user->position] ?? $user->position : '-' }}
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $user->email }}</td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.pengguna.edit', $user) }}" data-spin
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-50 dark:hover:bg-white/5 transition"
                                title="Edit">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <a href="{{ route('admin.reset-password.edit', $user) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"
                                title="Reset Password">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.pengguna.destroy', $user) }}"
                                onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                    title="Hapus">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-white/30">Belum ada akun {{ $roleLabel }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection
