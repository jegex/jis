<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

final class ProductDetail extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        if (! $product->is_published) {
            abort(404);
        }

        $this->product = $product->load('category', 'media', 'tags');
    }

    public function render()
    {
        $relatedProducts = Product::query()
            ->where('is_published', true)
            ->where('id', '!=', $this->product->id)
            ->when($this->product->category_id, fn ($q) => $q->where('category_id', $this->product->category_id))
            ->with('category', 'media')
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('livewire.product-detail', [
            'product' => $this->product,
            'relatedProducts' => $relatedProducts,
        ])->layout('layouts.app', [
            'model' => $this->product,
        ]);
    }
}
