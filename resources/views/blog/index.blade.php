<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Blog - {{ \App\Models\PlatformSetting::appName() }}</title>
    <meta name="description" content="Artikel dan berita terbaru seputar manajemen sekolah Islam terpadu.">
    @include('partials._vite')
@php $favicon = \App\Models\PlatformSetting::logo(); @endphp
@if ($favicon)
<link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
@endif
<style>
:root {
    --color-primary: {{ \App\Models\PlatformSetting::primaryColor() }};
    --color-secondary: {{ \App\Models\PlatformSetting::secondaryColor() }};
    --color-primary-dark: {{ \App\Models\PlatformSetting::darken(\App\Models\PlatformSetting::primaryColor(), 0.88) }};
    --color-secondary-dark: {{ \App\Models\PlatformSetting::darken(\App\Models\PlatformSetting::secondaryColor(), 0.88) }};
}
</style>
</head>
<body class="font-sans antialiased bg-white text-slate-900" style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">

    {{-- HEADER --}}
    <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/80 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
<img src="{{ \App\Models\PlatformSetting::logo() ? asset('storage/' . \App\Models\PlatformSetting::logo()) : asset('images/logo.svg') }}" alt="Logo" class="h-9 w-9">
<div>
<p class="text-sm font-bold leading-tight text-primary-dark">{{ \App\Models\PlatformSetting::appName() }}</p>
@if (\App\Models\PlatformSetting::tagline())
<p class="text-xs text-slate-500">{{ \App\Models\PlatformSetting::tagline() }}</p>
@endif
                    </div>
                </a>

                <nav class="hidden items-center gap-6 text-sm text-slate-600 md:flex">
                    <a href="{{ route('home') }}#fitur" class="hover:text-primary">Fitur</a>
                    <a href="{{ route('blog.index') }}" class="text-primary font-semibold">Blog</a>
                    <a href="{{ route('home') }}#harga" class="hover:text-primary">Harga</a>
                    <a href="{{ route('home') }}#faq" class="hover:text-primary">FAQ</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route(auth()->user()->role === 'superadmin' ? 'platform.dashboard' : auth()->user()->role . '.dashboard') }}"
                            class="rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('auth.login') }}"
                            class="hidden rounded-lg border border-primary-dark px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary/10 sm:inline-block">
                            Masuk
                        </a>
                        <a href="{{ route('auth.register') }}"
                            class="rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- BLOG HEADER --}}
    <section class="bg-gradient-to-b from-primary/10 to-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">Blog</h1>
                <p class="mt-4 text-lg text-slate-600">Artikel dan berita terbaru seputar manajemen sekolah Islam terpadu.</p>
            </div>
        </div>
    </section>

    {{-- BLOG POSTS --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        @if ($posts->count() === 0)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
                <div class="text-4xl">📝</div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Belum ada artikel</h3>
                <p class="mt-2 text-sm text-slate-500">Artikel akan segera hadir. Nantikan update dari kami.</p>
            </div>
        @else
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('blog.show', $post->slug) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary/30 hover:shadow-md">
                        @if ($post->featured_image)
                            <div class="mb-4 overflow-hidden rounded-xl">
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="h-48 w-full object-cover transition group-hover:scale-105">
                            </div>
                        @else
                            <div class="mb-4 flex h-48 items-center justify-center rounded-xl bg-primary/10">
                                <span class="text-4xl">📖</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2 mb-3">
                            <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary-dark">{{ ucfirst($post->category) }}</span>
                            <span class="text-xs text-slate-400">{{ $post->published_at->diffForHumans() }}</span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900 group-hover:text-primary">{{ $post->title }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 line-clamp-3">{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 150) }}</p>
                        <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary">{{ strtoupper(substr($post->author->name, 0, 2)) }}</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-900">{{ $post->author->name }}</p>
                                <p class="text-[11px] text-slate-400">{{ number_format($post->visit_count) }} kunjungan</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </section>

    {{-- FOOTER --}}
    @include('partials.site-footer')

</body>
</html>
