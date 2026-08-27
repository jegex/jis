<div class="bg-white" x-data="{
        search: $wire.entangle('search').live,
        showFilter: false
    }">
    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 py-4 xl:px-8!">
            <h1 class="text-xl font-medium text-gray-900 dark:text-gray-100">{{ __('Products') }}</h1>
        </div>
    </section>

    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 py-6 xl:px-8!">

            {{-- Search + Filter Toggle Row --}}
            <div class="flex items-center gap-3">
                <x-button
                    icon="heroicon-o-adjustments-horizontal"
                    iconPosition="before"
                    variant="flat"
                    color="gray"
                    data-animate="fade-left"
                    @click="showFilter = !showFilter"
                    x-bind:class="showFilter ? 'bg-gray-300' : ''"
                >
                    <span class="transition-all duration-300 ease-out">
                        {{ __('Filter') }}
                    </span>
                </x-button></button>

                <x-button
                    x-show="$wire.categoryId || search"
                    x-cloak
                    x-transition
                    wire:click="resetFilters"
                    variant="flat"
                    color="gray"
                    icon="heroicon-o-x-mark"
                >
                    {{ __('Reset') }}
                    <span x-show="$wire.categoryId"
                          x-cloak
                          x-transition
                          class="text-xs text-gray-950"
                          x-text="$wire.categoryId ? 1 : 0">
                    </span>
                </x-button>

                <div class="relative flex-1" data-animate="fade-right">
                    <x-input
                        size="sm"
                        variant="flat"
                        color="gray"
                        icon="heroicon-o-magnifying-glass"
                        iconPosition="before"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search products...') }}"
                    />
                </div>
            </div>

            {{-- Filter Panel --}}
            <div x-show="showFilter"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
            >
                <div class="space-y-4 pt-6">
                    <div class="flex items-center gap-2">
                        <div class="flex flex-wrap gap-2 text-xs items-center">
                            <div class="text-gray-500">{{ __('Categories:') }}</div>
                            <div>
                                <x-button
                                    wire:click="filterByCategory(null)"
                                    variant="flat"
                                    color="gray"
                                    size="xs"
                                    :variant="is_null($categoryId) ? 'solid' : 'flat'">
                                    {{ __('All') }}
                                </x-button>
                                @foreach($categories as $category)
                                    <x-button
                                        wire:click="filterByCategory({{ $category->id }})"
                                        color="gray"
                                        size="xs"
                                        :variant="$categoryId === $category->id ? 'solid' : 'flat'"
                                    >
                                        {{ $category->name }}
                                    </x-button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex flex-wrap gap-2 text-xs items-center">
                            <div class="text-gray-500">{{ __('Status:') }}</div>
                            <div>
                                <x-button
                                    wire:click="filterByReleaseStatus(null)"
                                    variant="flat"
                                    color="gray"
                                    size="xs"
                                    :variant="is_null($releaseStatus) ? 'solid' : 'flat'">
                                    {{ __('All') }}
                                </x-button>
                                <x-button
                                    wire:click="filterByReleaseStatus('regular')"
                                    color="gray"
                                    size="xs"
                                    :variant="$releaseStatus === 'regular' ? 'solid' : 'flat'"
                                >
                                    {{ __('Regular') }}
                                </x-button>
                                <x-button
                                    wire:click="filterByReleaseStatus('preorder')"
                                    color="warning"
                                    size="xs"
                                    :variant="$releaseStatus === 'preorder' ? 'solid' : 'flat'"
                                >
                                    {{ __('Preorder') }}
                                </x-button>
                                <x-button
                                    wire:click="filterByReleaseStatus('released')"
                                    color="success"
                                    size="xs"
                                    :variant="$releaseStatus === 'released' ? 'solid' : 'flat'"
                                >
                                    {{ __('Released') }}
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container pb-10 border-x border-x-gray-200 xl:px-8!">

            <div class="flex flex-col lg:flex-row items-center justify-between py-6 gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>{{ __('Sort by') }}:</span>
                        <select wire:model.live="sort"
                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <option value="newest">{{ __('Newest') }}</option>
                            <option value="oldest">{{ __('Oldest') }}</option>
                            <option value="price_asc">{{ __('Price: Low to High') }}</option>
                            <option value="price_desc">{{ __('Price: High to Low') }}</option>
                            <option value="name_asc">{{ __('Name: A-Z') }}</option>
                            <option value="name_desc">{{ __('Name: Z-A') }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span>{{ __('Items found:') }}</span>
                        <span class="text-gray-700 py-1 px-2 bg-gray-100 rounded">{{ $products->total() }}</span>
                    </div>
                </div>

                @if($products->hasPages())
                    <div>
                        {{ $products->links('components.paginate') }}
                    </div>
                @endif
            </div>

            @if($products->isNotEmpty())
                <div wire:loading.class="opacity-30 pointer-events-none" class="relative transition-opacity duration-300">
                    <div wire:loading.flex
                        wire:target="search, sort, filterByCategory, resetFilters"
                        class="absolute inset-0 z-10 items-center justify-center bg-white/60 rounded-xl">
                        <x-heroicon-o-arrow-path class="w-8 h-8 text-primary animate-spin" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-stagger>
                        @foreach($products as $product)
                            <x-cards.content-card
                                :item="$product"
                                mediaCollection="cover"
                                titleRoute="products.show"
                        buttonRoute="checkout.create"
                        buttonLabel="{{ __('Buy now') }}"
                                :showPrice="true"
                                data-stagger-item
                            />
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-16 text-gray-400">
                    <p>{{ __('No products found.') }}</p>
                </div>
            @endif

            @if($products->hasPages())
                <div class="mt-12">
                    {{ $products->links('components.paginate') }}
                </div>
            @endif
        </div>
    </section>
</div>
