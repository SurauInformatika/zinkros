@extends('layouts.guest')

@section('title', 'Daftar Sekolah - ' . \App\Models\PlatformSetting::appName())

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">{{ \App\Models\PlatformSetting::appName() }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Daftarkan sekolah Anda, gratis 14 hari trial</p>
        </div>

        <div class="bg-white dark:bg-[#141414] rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-8">
            <div class="flex justify-end mb-2">
                <button onclick="toggleDarkMode()" class="p-2 -mt-2 -mr-2 rounded-lg text-slate-400 dark:text-white/40 hover:bg-slate-100 dark:hover:bg-white/10 transition" title="Toggle dark mode">
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            <h2 class="text-lg font-semibold mb-1">Daftar Sekolah Anda</h2>
            <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Cukup isi data berikut. Akun admin sekolah akan dibuat otomatis.</p>

            <form method="POST" action="{{ route('auth.register.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="school_name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Sekolah</label>
                    <input id="school_name" type="text" name="school_name" value="{{ old('school_name') }}" required autofocus
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('school_name') border-red-400 @enderror">
                    @error('school_name')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="education_level" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jenjang Pendidikan</label>
                    <select id="education_level" name="education_level" required
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('education_level') border-red-400 @enderror">
                        <option value="">Pilih jenjang pendidikan</option>
                        @foreach (\App\Models\School::educationLevels() as $key => $meta)
                            <option value="{{ $key }}" {{ old('education_level') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Jenjang menentukan pilihan kelas yang tersedia saat mengelola kelas.</p>
                    @error('education_level')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Admin Sekolah</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">No. WhatsApp (opsional)</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('phone') border-red-400 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30">
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Daftarkan Sekolah
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-white/40">
                Sudah punya akun?
                <a href="{{ route('auth.login') }}" class="text-primary dark:text-primary hover:underline">Masuk</a>
            </p>
        </div>
    </div>
</div>
@endsection
