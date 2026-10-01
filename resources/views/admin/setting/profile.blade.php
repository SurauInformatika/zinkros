@extends('layouts.app')

@section('title', 'Profil Saya')

@php
    $tabs = [
        ['route' => 'admin.setting.profile', 'label' => 'Profil', 'active' => true],
        ['route' => 'admin.setting.password', 'label' => 'Password', 'active' => false],
        ['route' => 'admin.setting.school', 'label' => 'Sekolah', 'active' => false],
        ['route' => 'admin.setting.billing', 'label' => 'Paket & Billing', 'active' => false],
    ];
@endphp

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Pengaturan</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola profil dan pengaturan akun Anda.</p>
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

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <h2 class="text-lg font-semibold mb-1">Edit Profil</h2>
    <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Perbarui nama dan email Anda.</p>

    <form method="POST" action="{{ route('admin.setting.profile.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Profil
            </button>
        </div>
    </form>
</div>
@endsection
