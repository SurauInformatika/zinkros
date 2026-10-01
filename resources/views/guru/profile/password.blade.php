@extends('layouts.app')

@section('title', 'Ubah Password')

@php
    $tabs = [
        ['route' => 'guru.profile.index', 'label' => 'Profil', 'active' => false],
        ['route' => 'guru.profile.password', 'label' => 'Password', 'active' => true],
    ];
@endphp

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Profil Saya</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola data profil Anda.</p>
</div>

<div class="flex gap-1 mb-6 p-1 bg-slate-100 dark:bg-white/5 rounded-xl w-fit">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-150
                {{ $tab['active'] ? 'bg-white dark:bg-[#141414] text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>

@if (session('status'))
<div class="max-w-xl mb-4 rounded-xl bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('status') }}</div>
@endif

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <h2 class="text-lg font-semibold mb-1">Ubah Password</h2>
    <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Pastikan akun Anda tetap aman.</p>

    <form id="passwordForm" method="POST" action="{{ route('guru.profile.password.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Saat Ini</label>
            <input id="current_password" type="password" name="current_password" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('current_password') border-red-400 @enderror">
            @error('current_password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" id="passwordBtn"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Ubah Password
            </button>
        </div>
    </form>
</div>
@endsection
