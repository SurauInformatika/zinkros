<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\PageVisit;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()
            ->latestFirst()
            ->with('author')
            ->paginate(9);

        $posts->getCollection()->transform(function ($post) {
            $post->visit_count = PageVisit::countVisits('blog_show', $post->id);
            return $post;
        });

        return view('blog.index', compact('posts'));
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->is_published, 404);

        $post->load('author');

        $visitStats = [
            'total' => PageVisit::countVisits('blog_show', $post->id),
            'today' => PageVisit::countVisits('blog_show', $post->id, 'today'),
            'week' => PageVisit::countVisits('blog_show', $post->id, 'week'),
            'month' => PageVisit::countVisits('blog_show', $post->id, 'month'),
        ];

        $dailyVisits = PageVisit::dailyVisits('blog_show', $post->id, 30);

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->latestFirst()
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'related', 'visitStats', 'dailyVisits'));
    }
}
