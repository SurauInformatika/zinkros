<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $route = $request->route();
            $name = $route?->getName();

            if ($name === 'home') {
                PageVisit::track('landing');
            } elseif ($name === 'blog.show') {
                $post = $route->parameter('post');
                if ($post) {
                    PageVisit::track('blog_show', $post->id);
                }
            } elseif ($name === 'blog.index') {
                PageVisit::track('blog');
            }
        }

        return $response;
    }
}
