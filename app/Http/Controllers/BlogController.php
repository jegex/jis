<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;

final class BlogController extends Controller
{
    public function show(Post $post)
    {
        if (! $post->isPublished()) {
            abort(404);
        }

        $post->load('category', 'author', 'media', 'tags');

        $relatedPosts = Post::query()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->with('category', 'author', 'media')
            ->latest('published_at')
            ->take(3)
            ->get();

        $recentPosts = Post::query()
            ->where('id', '!=', $post->id)
            ->with('category', 'author', 'media')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts', 'recentPosts'))
            ->with('model', $post);
    }
}
