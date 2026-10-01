@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Blog</h1>
        <p class="text-sm text-slate-500 dark:text-white/40">Kelola artikel blog publik.</p>
    </div>
    <a href="{{ route('platform.blog.create') }}" class="rounded-lg bg-primary-dark px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
        + Tulis Artikel
    </a>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg bg-primary/10 border border-primary/20 p-4 text-sm text-primary-dark">{{ session('success') }}</div>
@endif

<div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/60">Judul</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/60">Kategori</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/60">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/60">Views</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/60">Tanggal</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-white/60">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($posts as $post)
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900 dark:text-white max-w-xs truncate">{{ $post->title }}</p>
                            <p class="text-xs text-slate-400">{{ $post->author->name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 dark:bg-white/10 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-white/60">{{ ucfirst($post->category) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($post->is_published)
                                <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary-dark">Published</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-white/60">{{ number_format($post->visit_count) }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-white/40">{{ $post->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($post->is_published)
                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-white/60 hover:bg-slate-50 dark:hover:bg-white/5 transition">Lihat</a>
                                @endif
                                <a href="{{ route('platform.blog.edit', $post) }}" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-white/60 hover:bg-slate-50 dark:hover:bg-white/5 transition">Edit</a>
                                <form action="{{ route('platform.blog.destroy', $post) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            <p class="text-2xl mb-2">📝</p>
                            <p>Belum ada artikel.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
