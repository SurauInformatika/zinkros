@extends('layouts.app')

@section('title', 'Dashboard Murid')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $school?->roleLabel('murid') ?? 'Murid' }} Dashboard</h1>
    <div class="flex items-center gap-3">
        <a href="{{ route('murid.hafalan') }}" class="rounded-lg border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-medium text-primary hover:bg-primary/15 dark:border-primary/30 dark:bg-primary/100/10 dark:text-primary">
            Hafalan Al-Quran
        </a>
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white/80">
                Keluar
            </button>
        </form>
    </div>
</div>

<div class="rounded-xl bg-white border border-slate-200 p-5 dark:border-white/10 dark:bg-[#141414]">
    <p class="text-sm text-slate-500 dark:text-white/50">Selamat datang, <span class="font-semibold text-slate-900 dark:text-white">{{ $name }}</span> di {{ $school?->name }}.</p>
    <p class="mt-2 text-sm text-slate-600 dark:text-white/40">Dashboard {{ $roleLabel }} sedang disiapkan. Fitur (nilai, absensi, hafalan, dan lainnya) akan tampil di sini.</p>
</div>
@endsection