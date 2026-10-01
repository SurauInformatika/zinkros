@extends('layouts.app')

@section('title', 'Dashboard Staf')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Dashboard Staf</h1>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button type="submit" class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 dark:text-white/80 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/10">
            Keluar
        </button>
    </form>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
    <p class="text-sm text-slate-600 dark:text-white/60">
        Selamat datang, <span class="font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
        ({{ auth()->user()->position }}).
    </p>
    <p class="mt-2 text-sm text-slate-500 dark:text-white/40">Dashboard staf akan diisi sesuai kebutuhan operasional sekolah.</p>
</div>
@endsection
