<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CategoryType;
use App\Livewire\Concerns\HasListFilters;
use App\Models\Category;
use App\Models\Post;
use Livewire\Component;

final class BlogList extends Component
{
    use HasListFilters;

    public ?int $categoryId = null;

    protected $queryString = ['categoryId', 'search', 'sort'];

    public function render()
    {
        $query = Post::published()
            ->with('category', 'author', 'media', 'tags');

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('excerpt', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%');
            });
        }

        match ($this->sort) {
            'oldest' => $query->oldest('published_at'),
            'name_asc' => $query->orderBy('title->'.app()->getLocale()),
            'name_desc' => $query->orderBy('title->'.app()->getLocale(), 'desc'),
            default => $query->latest('published_at'),
        };

        $featuredPosts = Post::published()->latest()->take(5)->get();
        $posts = $query->paginate(9);
        $categories = Category::where('type', CategoryType::Post->value)
            ->published()
            ->whereHas('posts')
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->get();

        return view('livewire.blog-list', compact('posts', 'categories', 'featuredPosts'))
            ->layout('layouts.app');
    }

    public function filterByCategory(?int $id)
    {
        $this->categoryId = $id;
        $this->resetPage();
    }
}
