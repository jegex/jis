<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;

final class BlogController extends Controller
{
    public function show(Post $post)
    {
        if (! $post->is_published) {
            abort(404);
        }

        $post->load('category', 'author', 'media', 'tags');

        $relatedPosts = Post::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take(3)
            ->get();

        $recentPosts = Post::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts', 'recentPosts'))
            ->with('model', $post);
    }
}
