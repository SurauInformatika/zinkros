@extends('layouts.app')

@section('title', 'Reset Password — ' . $user->name)

@section('content')
<div class="mb-6">
    <a href="javascript:history.back()" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali</a>
    <h1 class="mt-2 text-2xl font-bold">Reset Password</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Ubah password untuk <span class="font-semibold">{{ $user->name }}</span> ({{ $user->email }}).</p>
</div>

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.reset-password.update', $user) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required autofocus
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Reset Password
            </button>
            <a href="javascript:history.back()" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>
@endsection
