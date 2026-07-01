<div>
    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 py-3 xl:px-8!">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('products.index') }}"
                   class="hover:text-primary transition-colors">{{ __('Products') }}</a>
                @if($product->category)
                    <span>/</span>
                    <span class="text-gray-400">{{ $product->category->name }}</span>
                @endif
            </nav>
        </div>
    </section>

    <section class="border-b border-b-gray-200">
        <div class="container py-6 border-b border-x border-x-gray-200 border-b-gray-200">
            @if($product->category)
                <span
                    class="inline-block text-secondary font-semibold text-xs tracking-widest uppercase mb-2">{{ $product->category->name }}</span>
            @endif
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white font-display leading-tight">{{ $product->title }}</h1>
        </div>

        <div class="container border-x border-x-gray-200 xl:px-0!">
            <div class="lg:grid lg:grid-cols-5 lg:divide-x divide-gray-200">
                <div class="lg:px-8 py-10 lg:col-span-3 space-y-8">
                    @php
                        $galleryImages = collect()
                            ->when(
                                $product->getFirstMediaUrl('cover'),
                                fn ($col) => $col->push([
                                    'url' => $product->getFirstMediaUrl('cover'),
                                    'url_small' => $product->getFirstMediaUrl('cover', 'small'),
                                    'alt' => $product->title,
                                ])
                            )
                            ->concat(
                                $product->getMedia('gallery')->map(fn ($media) => [
                                    'url' => $media->getUrl(),
                                    'url_small' => $media->getUrl('small'),
                                    'alt' => $product->title.' - '.($media->name ?: $loop->iteration),
                                ])
                            )
                            ->values()
                            ->toArray();
                    @endphp

                    <x-product-gallery :images="$galleryImages" />

                    @if($product->short_description)
                        <div data-animate="fade-up" data-delay="0.1">
                            <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">{{ $product->short_description }}</p>
                        </div>
                    @endif

                    @if($product->description)
                        <div data-animate="fade-up" data-delay="0.2"
                             class="prose max-w-none break-words prose-headings:font-display prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm">
                            {!! $product->content !!}
                        </div>
                    @endif
                </div>
                <div class="lg:px-8 py-10 lg:col-span-2 mt-8 lg:mt-0">
                    <div class="lg:sticky lg:top-24 space-y-6" data-animate="fade-up" data-delay="0.1">
                        <div class="pb-6 border-b border-gray-200 dark:border-gray-700">
                            <p class="text-4xl font-bold text-primary">{{ Str::price($product->price, $product->currency_code) }}</p>
                        </div>

                        <div class="pt-4">
                            <a href="{{ route('checkout.create', $product) }}"

                               class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                                <x-heroicon-o-shopping-cart class="w-5 h-5"/>
                                {{ __('Buy Now') }}
                            </a>
                        </div>

                        <div class="pt-2 space-y-2 text-sm text-gray-400">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-shield-check class="w-4 h-4 text-success"/>
                                <span>{{ __('Secure checkout with Midtrans') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-success"/>
                                <span>{{ __('Instant download after payment') }}</span>
                            </div>
                        </div>

                        @if($product->tags->isNotEmpty())
                            <div>
                                <h3 class="text-xs font-semibold text-gray-400 tracking-widest uppercase mb-2">{{ __('Tags') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($product->tags as $tag)
                                        <span
                                            class="px-3 py-1 bg-primary-light text-primary rounded-full text-sm font-medium">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($relatedProducts->isNotEmpty())
        <section class="border-b border-b-gray-200">
            <div class="container py-16 border-x border-x-gray-200 xl:px-8!">
                <x-sections.section-header
                    label="{{ __('Related Products') }}"
                    title="{{ __('More Products You Might Like') }}"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-stagger>
                    @foreach($relatedProducts as $related)
                        <x-cards.content-card :item="$related" mediaCollection="cover" titleRoute="products.show"
                                              buttonRoute="checkout.create" buttonLabel="{{ __('Buy now') }}" :showPrice="true"
                                              data-stagger-item/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
