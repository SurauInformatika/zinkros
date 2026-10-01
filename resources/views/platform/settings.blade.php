@extends('layouts.app')

@section('title', 'Pengaturan Platform')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Pengaturan Platform</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola identitas aplikasi, logo, dan warna tema platform. Konten beranda diatur pada halaman Konten Beranda.</p>
</div>

<form method="POST" action="{{ route('platform.settings.update') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <h2 class="text-lg font-semibold mb-1">Identitas Aplikasi</h2>
        <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Nama aplikasi ini ditampilkan di sidebar, halaman login, dan halaman publik.</p>

        <div>
            <label for="app_name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Aplikasi</label>
            <input id="app_name" type="text" name="app_name" value="{{ old('app_name', $settings->app_name) }}" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('app_name') border-red-400 @enderror">
            @error('app_name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tagline" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tagline</label>
            <input id="tagline" type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}"
                maxlength="100" placeholder="mis. Sekolah Islam Terpadu"
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('tagline') border-red-400 @enderror">
            <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Ditampilkan di bawah nama aplikasi pada header halaman publik. Kosongkan untuk menyembunyikannya.</p>
            @error('tagline')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <h2 class="text-lg font-semibold mb-1">Logo Aplikasi</h2>
        <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Logo ditampilkan di sidebar dan sebagai favicon.</p>

        <div id="logo-dropzone"
            class="relative flex flex-col items-center justify-center w-full h-44 rounded-xl border-2 border-dashed border-slate-300 dark:border-white/15 bg-slate-50 dark:bg-white/5 cursor-pointer transition-all duration-200 hover:border-primary dark:hover:border-primary/40 hover:bg-primary/10/50 dark:hover:bg-primary/100/5 group overflow-hidden">
            <input type="file" id="logo-input" name="logo" accept="image/png,image/jpeg,image/svg+xml" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">

            @if ($settings->logo)
                <img id="logo-preview-img" src="{{ asset('storage/' . $settings->logo) }}" alt="Logo"
                    class="absolute inset-0 w-full h-full object-contain p-4">
                <div id="logo-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 opacity-0 transition-opacity duration-200 group-hover:opacity-100 z-10">
                    <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm font-medium text-white">Ganti logo</p>
                </div>
            @else
                <div id="logo-placeholder" class="pointer-events-none text-center">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-400 dark:text-white/25 group-hover:text-primary dark:group-hover:text-primary transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-600 dark:text-white/50 group-hover:text-primary dark:group-hover:text-primary transition">
                        <span class="font-semibold">Klik untuk upload</span> atau seret ke sini
                    </p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-white/30">PNG, JPG, atau SVG. Maks 2MB.</p>
                </div>
            @endif
        </div>
        @error('logo')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <h2 class="text-lg font-semibold mb-1">Warna Tema</h2>
        <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Warna primer dan sekunder diterapkan ke sidebar, tombol, dan elemen aksen pada halaman platform dan publik.</p>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="primary_color" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-2">Warna Primer</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="primary_color_display" value="{{ old('primary_color', $settings->primary_color) }}"
                        class="w-12 h-10 rounded-lg border border-slate-300 dark:border-white/10 cursor-pointer"
                        onchange="document.getElementById('primary_color').value = this.value">
                    <input type="text" id="primary_color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}" required
                        class="flex-1 rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('primary_color') border-red-400 @enderror"
                        oninput="document.getElementById('primary_color_display').value = this.value"
                        pattern="^#[0-9A-Fa-f]{6}$">
                </div>
                @error('primary_color')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="secondary_color" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-2">Warna Sekunder</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="secondary_color_display" value="{{ old('secondary_color', $settings->secondary_color) }}"
                        class="w-12 h-10 rounded-lg border border-slate-300 dark:border-white/10 cursor-pointer"
                        onchange="document.getElementById('secondary_color').value = this.value">
                    <input type="text" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}" required
                        class="flex-1 rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('secondary_color') border-red-400 @enderror"
                        oninput="document.getElementById('secondary_color_display').value = this.value"
                        pattern="^#[0-9A-Fa-f]{6}$">
                </div>
                @error('secondary_color')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10">
            <p class="text-xs font-semibold text-slate-500 dark:text-white/40 mb-3">Preview</p>
            <div class="flex items-center gap-3">
                <div id="preview-primary" class="w-10 h-10 rounded-lg shadow-sm" style="background-color: {{ old('primary_color', $settings->primary_color) }}"></div>
                <div id="preview-secondary" class="w-10 h-10 rounded-lg shadow-sm" style="background-color: {{ old('secondary_color', $settings->secondary_color) }}"></div>
                <div class="flex-1 h-10 rounded-lg bg-gradient-to-r shadow-sm"
                    id="preview-gradient"
                    style="background: linear-gradient(135deg, {{ old('primary_color', $settings->primary_color) }}, {{ old('secondary_color', $settings->secondary_color) }})"></div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan Pengaturan
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const primary = document.getElementById('primary_color');
        const secondary = document.getElementById('secondary_color');
        const primaryDisplay = document.getElementById('primary_color_display');
        const secondaryDisplay = document.getElementById('secondary_color_display');
        const previewPrimary = document.getElementById('preview-primary');
        const previewSecondary = document.getElementById('preview-secondary');
        const previewGradient = document.getElementById('preview-gradient');
        const sidebarMobile = document.getElementById('sidebar');
        const sidebarDesktop = document.getElementById('sidebar-desktop');

        function updateColors() {
            const p = primary.value;
            const s = secondary.value;

            previewPrimary.style.backgroundColor = p;
            previewSecondary.style.backgroundColor = s;
            previewGradient.style.background = `linear-gradient(135deg, ${p}, ${s})`;

            if (sidebarMobile) sidebarMobile.style.backgroundColor = p;
            if (sidebarDesktop) sidebarDesktop.style.backgroundColor = p;

            document.documentElement.style.setProperty('--color-primary', p);
            document.documentElement.style.setProperty('--color-secondary', s);
        }

        primary.addEventListener('input', () => { primaryDisplay.value = primary.value; updateColors(); });
        secondary.addEventListener('input', () => { secondaryDisplay.value = secondary.value; updateColors(); });
        primaryDisplay.addEventListener('input', () => { if (/^#[0-9A-Fa-f]{6}$/.test(primaryDisplay.value)) { primary.value = primaryDisplay.value; updateColors(); } });
        secondaryDisplay.addEventListener('input', () => { if (/^#[0-9A-Fa-f]{6}$/.test(secondaryDisplay.value)) { secondary.value = secondaryDisplay.value; updateColors(); } });
        primary.addEventListener('change', () => { primaryDisplay.value = primary.value; updateColors(); });
        secondary.addEventListener('change', () => { secondaryDisplay.value = secondary.value; updateColors(); });

        const dropzone = document.getElementById('logo-dropzone');
        const fileInput = document.getElementById('logo-input');
        const previewImg = document.getElementById('logo-preview-img');
        const placeholder = document.getElementById('logo-placeholder');
        const sidebarLogoMobile = document.querySelector('#sidebar img[alt="Logo"]');
        const sidebarLogoDesktop = document.querySelector('#sidebar-desktop img[alt="Logo"]');

        function showPreview(file) {
            if (!file || !file.type.startsWith('image/')) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB.');
                fileInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const url = e.target.result;

                if (previewImg) {
                    previewImg.src = url;
                } else {
                    const img = document.createElement('img');
                    img.id = 'logo-preview-img';
                    img.src = url;
                    img.alt = 'Logo';
                    img.className = 'absolute inset-0 w-full h-full object-contain p-4';
                    if (placeholder) placeholder.remove();
                    dropzone.appendChild(img);
                }

                if (sidebarLogoMobile) sidebarLogoMobile.src = url;
                if (sidebarLogoDesktop) sidebarLogoDesktop.src = url;
            };
            reader.readAsDataURL(file);
        }

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) showPreview(e.target.files[0]);
        });

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-primary', 'dark:border-primary/40', 'bg-primary/10', 'dark:bg-primary/100/5');
        });

        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-primary', 'dark:border-primary/40', 'bg-primary/10', 'dark:bg-primary/100/5');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-primary', 'dark:border-primary/40', 'bg-primary/10', 'dark:bg-primary/100/5');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showPreview(e.dataTransfer.files[0]);
            }
        });
    });
</script>
@endsection