@extends('layouts.app')

@section('title', 'Edit Staff')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.staff.index') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar staff</a>
    <h1 class="mt-2 text-2xl font-bold">Edit Staff</h1>
</div>

<div class="max-w-2xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name', $staff->name) }}" required autofocus
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jabatan</label>
            <input id="position" type="text" name="position" value="{{ old('position', $staff->position) }}" required
                placeholder="Contoh: Sekuriti, CS, Staf Administrasi"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('position') border-red-400 @enderror">
            @error('position')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $staff->email) }}" required
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Baru <span class="text-xs text-slate-400 dark:text-white/30">(kosongkan jika tidak diubah)</span></label>
                <input id="password" type="password" name="password" autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.staff.index') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>
@endsection
