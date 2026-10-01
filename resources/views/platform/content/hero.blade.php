@extends('layouts.app')

@section('title', 'Hero')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Hero</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Teks yang ditampilkan di bagian hero halaman beranda. Kosongkan untuk memakai nilai default.</p>
</div>

@include('platform.partials.form-alert')

<form method="POST" action="{{ route('platform.content.hero.update') }}" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <div class="space-y-5">
            <div>
                <label for="hero_badge" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Badge</label>
                <input id="hero_badge" type="text" name="hero_badge" value="{{ old('hero_badge', $settings->hero_badge) }}" maxlength="100"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_badge') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400 dark:text-white/30">mis. "Solusi Digital untuk Sekolah Islam"</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="hero_title" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul Utama</label>
                    <input id="hero_title" type="text" name="hero_title" value="{{ old('hero_title', $settings->hero_title) }}" maxlength="255"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_title') border-red-400 @enderror">
                </div>
                <div>
                    <label for="hero_title_highlight" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul Sorotan</label>
                    <input id="hero_title_highlight" type="text" name="hero_title_highlight" value="{{ old('hero_title_highlight', $settings->hero_title_highlight) }}" maxlength="100"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_title_highlight') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Bagian judul berwarna tema.</p>
                </div>
            </div>

            <div>
                <label for="hero_desc" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Deskripsi</label>
                <textarea id="hero_desc" name="hero_desc" rows="3" maxlength="1000"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y @error('hero_desc') border-red-400 @enderror">{{ old('hero_desc', $settings->hero_desc) }}</textarea>
            </div>

            <div>
                <label for="hero_microcopy" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Microcopy CTA</label>
                <input id="hero_microcopy" type="text" name="hero_microcopy" value="{{ old('hero_microcopy', $settings->hero_microcopy) }}" maxlength="255"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_microcopy') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400 dark:text-white/30">mis. "Tanpa kartu kredit · Setup 5 menit · Support WhatsApp"</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="hero_cta_guest" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tombol (Belum Login)</label>
                    <input id="hero_cta_guest" type="text" name="hero_cta_guest" value="{{ old('hero_cta_guest', $settings->hero_cta_guest) }}" maxlength="100"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_cta_guest') border-red-400 @enderror">
                </div>
                <div>
                    <label for="hero_cta_features" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tombol Teks Utama</label>
                    <input id="hero_cta_features" type="text" name="hero_cta_features" value="{{ old('hero_cta_features', $settings->hero_cta_features) }}" maxlength="100"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_cta_features') border-red-400 @enderror">
                </div>
                <div>
                    <label for="hero_cta_auth" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tombol (Sudah Login)</label>
                    <input id="hero_cta_auth" type="text" name="hero_cta_auth" value="{{ old('hero_cta_auth', $settings->hero_cta_auth) }}" maxlength="100"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('hero_cta_auth') border-red-400 @enderror">
                </div>
            </div>
        </div>

        <label class="mt-6 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span>
                <span class="block text-sm font-semibold text-slate-700 dark:text-white/70">Tampilkan di landing page</span>
                <span class="block text-xs text-slate-500 dark:text-white/40 mt-0.5">Jika dinonaktifkan, bagian hero disembunyikan dari halaman beranda.</span>
            </span>
            <input type="checkbox" name="show_hero" value="1" @checked(old('show_hero', (bool) $settings->show_hero))
                class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
        </label>
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan Hero
        </button>
    </div>
</form>
@endsection