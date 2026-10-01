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
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.pengguna.edit', $user) }}"
                                class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                Edit
                            </a>
                            <a href="{{ route('admin.reset-password.edit', $user) }}"
                                class="rounded-lg border border-amber-200 dark:border-amber-500/20 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                                Reset Password
                            </a>
                            <form method="POST" action="{{ route('admin.pengguna.destroy', $user) }}"
                                onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg border border-red-200 dark:border-red-500/20 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                    Hapus
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
