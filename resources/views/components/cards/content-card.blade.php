@props([
    'item' => null,
    'mediaCollection' => 'featured_image',
    'mediaVariant' => 'thumb',
    'titleRoute' => null,
    'buttonRoute' => null,
    'buttonLabel' => __('Read More'),
    'showMeta' => false,
    'showPrice' => false,
])

<div {{ $attributes->merge(['class' => 'group relative bg-gray-100 hover:bg-gray-200 border border-gray-100 overflow-hidden', 'data-stagger-item' => true]) }}>
    <a href="{{ $titleRoute ? route($titleRoute, $item) : '#' }}" class="absolute inset-0 z-10"></a>
    <div class="p-5">
        @if($item && $item->getFirstMediaUrl($mediaCollection))
            <div class="relative overflow-hidden aspect-video">
                @if($item instanceof \App\Models\Product && $item->isPreorder())
                    <div class="absolute top-2 left-2 z-20 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                        {{ __('Preorder') }}
                    </div>
                @endif
                <img src="{{ $item->getFirstMediaUrl($mediaCollection, $mediaVariant) }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform will-change-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
        @else
            <div class="w-full aspect-video bg-primary-light flex items-center justify-center text-primary/40">
                <x-heroicon-o-photo class="w-12 h-12" />
            </div>
        @endif
        <div class="my-4">
            <h3 class="relative font-semibold text-gray-900 group-hover:text-primary truncate mb-1">
                <a href="{{ $titleRoute ? route($titleRoute, $item) : '#' }}" class="transition-colors">{{ $item->title }}</a>
            </h3>
            @if($item->excerpt)
                <p class="text-sm text-gray-500 line-clamp-2">{{ $item->excerpt }}</p>
            @elseif($item->description)
                <p class="text-sm text-gray-500 line-clamp-2">{{ strip_tags($item->description) }}</p>
            @endif
            @if($showMeta && $item->category)
                <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-2 justify-between">
                    <span class="text-primary font-medium py-1 px-3 bg-primary-100">{{ $item->category->name }}</span>
                    <span>{{ $item->published_at?->format(setting('date_format', 'd M Y')) }}</span>
                </div>
            @endif
        </div>

        <x-button-card href="{{ $buttonRoute ? route($buttonRoute, $item) : '#' }}">
            @if($showPrice)
                <x-slot name="price">
                    {{ Str::price($item->price, $item->currency_code) }}
                </x-slot>
            @endif
            {{ $buttonLabel }}
        </x-button-card>
    </div>
</div>
