<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CategoryType;
use App\Livewire\Concerns\HasListFilters;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

final class ProductList extends Component
{
    use HasListFilters;

    public ?int $categoryId = null;

    public ?int $tagId = null;

    public ?string $releaseStatus = null;

    protected $queryString = ['categoryId', 'tagId', 'search', 'sort', 'releaseStatus'];

    public function render()
    {
        $query = Product::query()->with('category', 'media', 'tags');

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->tagId) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $this->tagId));
        }

        if ($this->releaseStatus) {
            match ($this->releaseStatus) {
                'regular' => $query->whereNull('release_date'),
                'preorder' => $query->where('release_date', '>', now()),
                'released' => $query->where('release_date', '<=', now())->whereNotNull('release_date'),
            };
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('short_description', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        match ($this->sort) {
            'oldest' => $query->oldest('created_at'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('title->'.app()->getLocale()),
            'name_desc' => $query->orderBy('title->'.app()->getLocale(), 'desc'),
            default => $query->latest('created_at'),
        };

        $products = $query->paginate(9);
        $categories = Category::where('type', CategoryType::Product->value)->where('is_published', true)->get();

        return view('livewire.product-list', compact('products', 'categories'))
            ->layout('layouts.app');
    }

    public function filterByCategory(?int $id)
    {
        $this->categoryId = $id;
        $this->resetPage();
    }

    public function filterByTag(?int $id)
    {
        $this->tagId = $id;
        $this->resetPage();
    }

    public function filterByReleaseStatus(?string $status)
    {
        $this->releaseStatus = $status;
        $this->resetPage();
    }
}
