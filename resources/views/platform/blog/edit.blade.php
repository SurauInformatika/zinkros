@extends('layouts.app')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('platform.blog.index') }}" class="text-sm text-slate-500 hover:text-primary">&larr; Kembali ke Blog</a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-2">Edit Artikel</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('platform.blog.update', $blog) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-white/80 mb-1">Judul *</label>
                        <input type="text" name="title" value="{{ old('title', $blog->title) }}" required
                            class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-white/80 mb-1">Kategori *</label>
                            <input type="text" name="category" value="{{ old('category', $blog->category) }}" required
                                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-white/80 mb-1">Gambar Sampul (URL)</label>
                            <input type="text" name="featured_image" value="{{ old('featured_image', $blog->featured_image) }}"
                                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-white/80 mb-1">Ringkasan</label>
                        <textarea name="excerpt" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-white/80 mb-1">Isi Artikel *</label>
                        <textarea name="body" rows="15" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary font-mono">{{ old('body', $blog->body) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $blog->is_published) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                        <label class="text-sm font-medium text-slate-700 dark:text-white/80">Publikasikan</label>
                        @if ($blog->published_at)
                            <span class="text-xs text-slate-400">· Dipublikasikan {{ $blog->published_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-primary-dark px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Update</button>
                <a href="{{ route('platform.blog.index') }}" class="rounded-lg border border-slate-300 dark:border-white/10 px-6 py-2.5 text-sm font-semibold text-slate-700 dark:text-white/70 transition hover:bg-slate-50 dark:hover:bg-white/5">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
