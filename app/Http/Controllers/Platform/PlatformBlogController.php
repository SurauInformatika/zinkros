<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\PageVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformBlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('author')
            ->latest()
            ->paginate(20);

        $posts->getCollection()->transform(function ($post) {
            $post->visit_count = PageVisit::countVisits('blog_show', $post->id);
            return $post;
        });

        return view('platform.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('platform.blog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'category' => 'required|string|max:100',
            'featured_image' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();
        $validated['school_id'] = null;
        $validated['views'] = 0;

        if (!empty($validated['is_published']) || $validated['is_published'] === '1') {
            $validated['is_published'] = true;
            $validated['published_at'] = now();
        } else {
            $validated['is_published'] = false;
        }

        BlogPost::create($validated);

        return redirect()->route('platform.blog.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(BlogPost $blog)
    {
        return view('platform.blog.edit', compact('blog'));
    }

    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'category' => 'required|string|max:100',
            'featured_image' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if (!empty($validated['is_published']) || $validated['is_published'] === '1') {
            $validated['is_published'] = true;
            if (is_null($blog->published_at)) {
                $validated['published_at'] = now();
            }
        } else {
            $validated['is_published'] = false;
            $validated['published_at'] = null;
        }

        $blog->update($validated);

        return redirect()->route('platform.blog.index')->with('success', 'Artikel berhasil diupdate.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();

        return redirect()->route('platform.blog.index')->with('success', 'Artikel berhasil dihapus.');
    }
}
