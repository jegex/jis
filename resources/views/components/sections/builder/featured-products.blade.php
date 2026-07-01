@props(['data' => [], 'items' => []])

@php
    $products = $items;
    $locale = app()->getLocale();
    $sectionLabel = $data['label'] ?? null;
    $sectionTitle = $data['title'] ?? null;
    $sectionDescription = $data['description'] ?? null;
    $showViewAll = $data['show_view_all'] ?? true;
@endphp

<section id="products" class="border-b border-b-gray-200">
    <div class="container py-16 border-x border-x-gray-200">
        <x-sections.section-header
            :label="locale_text($sectionLabel, $locale)"
            :title="locale_text($sectionTitle, $locale)"
            :description="locale_text($sectionDescription, $locale)"
            :actionText="$showViewAll ? __('View All Products') : null"
            :actionUrl="$showViewAll ? route('products.index') : null"
        />

        @if($products->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-stagger>
                @foreach($products as $product)
                    <x-cards.content-card
                        :item="$product"
                        mediaCollection="cover"
                        titleRoute="products.show"
                        buttonRoute="checkout.create"
                        buttonLabel="{{ __('Buy now') }}"
                        :showMeta="true"
                        :showPrice="true" />
                @endforeach
            </div>
        @else
            <div class="text-center py-16 text-gray-400">
                <p>{{ __('No products available yet.') }}</p>
            </div>
        @endif
    </div>
</section>
