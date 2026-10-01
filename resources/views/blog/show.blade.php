<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $post->title }} - Blog {{ \App\Models\PlatformSetting::appName() }}</title>
    <meta name="description" content="{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 160) }}">
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

    {{-- BREADCRUMB --}}
    <div class="border-b border-slate-100 bg-slate-50 py-4">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
                <span>/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-primary">Blog</a>
                <span>/</span>
                <span class="text-slate-900 font-medium truncate">{{ $post->title }}</span>
            </nav>
        </div>
    </div>

    {{-- ARTICLE --}}
    <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <header class="mb-10">
            <div class="flex items-center gap-3 mb-4">
                <span class="rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-primary-dark">{{ ucfirst($post->category) }}</span>
                <time class="text-sm text-slate-500">{{ $post->published_at->format('d M Y') }}</time>
                <span class="text-sm text-slate-400">· {{ number_format($visitStats['total']) }} kunjungan</span>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $post->title }}</h1>
            <div class="mt-6 flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/15 text-sm font-bold text-primary">{{ strtoupper(substr($post->author->name, 0, 2)) }}</div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $post->author->name }}</p>
                </div>
            </div>
        </header>

        @if ($post->featured_image)
            <div class="mb-10 overflow-hidden rounded-2xl">
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full object-cover">
            </div>
        @endif

        <div class="prose prose-lg prose-slate max-w-none">
            {!! nl2br(e($post->body)) !!}
        </div>
    </article>

    {{-- RELATED POSTS --}}
    @if ($related->count() > 0)
        <section class="border-t border-slate-100 bg-slate-50 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-8">Artikel Terkait</h2>
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($related as $rel)
                        <a href="{{ route('blog.show', $rel->slug) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary/30 hover:shadow-md">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary-dark">{{ ucfirst($rel->category) }}</span>
                                <span class="text-xs text-slate-400">{{ $rel->published_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary">{{ $rel->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600 line-clamp-2">{{ $rel->excerpt ?: Str::limit(strip_tags($rel->body), 100) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FOOTER --}}
    @include('partials.site-footer')

</body>
</html>
