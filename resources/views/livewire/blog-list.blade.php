<div class="bg-white" x-data="{
    search: $wire.entangle('search').live,
    showFilter: false
}">
    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 py-4 xl:px-8!">
            <h1 class="text-xl font-medium text-gray-900 dark:text-gray-100">{{ __('Blog') }}</h1>
        </div>
    </section>

    {{-- Featured Posts --}}
    <div class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 lg:px-0!">
            @if($featuredPosts->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 divide-x divide-gray-200 border-t border-t-gray-200">
                    <a href="{{ route('blog.show', $featuredPosts->first()) }}" class="group relative">
                        @if($featuredPosts->first()->getFirstMediaUrl('featured_image'))
                            <div class="relative aspect-video overflow-hidden">
                                <img src="{{ $featuredPosts->first()->getFirstMediaUrl('featured_image', 'thumb') }}" alt="{{ $featuredPosts->first()->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        @endif
                        <div class="py-4 px-4 lg:px-8">
                            @if($featuredPosts->first()->category)
                                <span class="text-secondary font-semibold text-xs tracking-widest uppercase">{{ $featuredPosts->first()->category->name }}</span>
                            @endif
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary mt-1">{{ $featuredPosts->first()->title }}</h3>
                            <p class="text-sm text-gray-500 py-2">{{ $featuredPosts->first()->created_at->format(setting('date_format', 'd M Y')) }}</p>
                            <p class="text-sm text-gray-500">{{ $featuredPosts->first()->excerpt }}</p>
                        </div>
                    </a>

                    <div class="flex flex-col grow divide-y divide-gray-200">
                        @foreach($featuredPosts as $post)
                            @if($post->isNot($featuredPosts->first()))
                                <div class="relative group">
                                    <a href="{{ route('blog.show', $post) }}" class="absolute inset-0"></a>
                                    <div class="py-4 px-4 lg:px-8 group-hover:bg-gray-100">
                                        <span class="flex items-center justify-between">
                                            @if($post->category)
                                                <span class="text-secondary font-semibold text-xs tracking-widest uppercase">{{ $post->category->name }}</span>
                                            @endif

                                            <span class="text-sm text-gray-500">{{ $post->created_at->format(setting('date_format', 'd M Y')) }}</span>
                                        </span>
                                        <h3 class="font-semibold text-gray-900 group-hover:text-primary my-2">{{ $post->title }}</h3>
                                        <p class="text-sm text-gray-500">{{ $post->excerpt }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Filters Posts --}}
    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 py-6 xl:px-8!">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-500" />
                </span>
                <input
                    class="w-full outline-none pl-10"
                    type="search"
                    wire:model.live.debounce.300ms="search" placeholder="{{ __('Search posts...') }}">
            </div>
        </div>
    </section>

    <section>
        <div class="container border-x border-x-gray-200 xl:px-0!">
            <div class="grid grid-cols-1 xl:grid-cols-3 divide-x divide-gray-200">
                <div class="xl:col-span-2">

                    {{-- Sort + Pagination Top --}}
                    <div class="flex items-center justify-between gap-4 py-6 px-4 xl:px-8">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ __('Sort by') }}:</span>
                                <select wire:model.live="sort"
                                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                    <option value="newest">{{ __('Newest') }}</option>
                                    <option value="oldest">{{ __('Oldest') }}</option>
                                    <option value="name_asc">{{ __('Title: A-Z') }}</option>
                                    <option value="name_desc">{{ __('Title: Z-A') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                <span>{{ __('Items found:') }}</span>
                                <span class="text-gray-700 py-1 px-2 bg-gray-100 rounded">{{ $posts->total() }}</span>
                            </div>
                        </div>

                        @if($posts->hasPages())
                            <div>
                                {{ $posts->links('components.paginate') }}
                            </div>
                        @endif
                    </div>

                    @if($posts->isNotEmpty())
                        <div wire:loading.class="opacity-30" class="relative transition-opacity duration-300">
                            <div wire:loading.flex
                                 wire:target="search, sort, filterByCategory, resetFilters"
                                 class="absolute inset-0 z-10 items-center justify-center bg-white/60 rounded-xl py-20">
                                <x-heroicon-o-arrow-path class="w-8 h-8 text-primary animate-spin" />
                            </div>

                            <div class="flex flex-col" data-stagger>
                                @foreach($posts as $post)
                                    <a href="{{ route('blog.show', $post) }}" class="group relative z-10 flex cursor-pointer flex-col items-center gap-2 border-b border-t border-transparent px-4 py-3 transition-colors hover:border-gray-200 hover:bg-gray-100 md:py-6 lg:flex-row lg:gap-8 xl:px-8">
                                        <div class="order-1 flex-1 lg:order-1">
                                            <h3 class="font-semibold text-gray-900 hover:text-primary">{{ $post->title }}</h3>
                                        </div>
                                        <div class="order-2 mb-1 lg:order-2 lg:mb-0 lg:w-40">
                                            @if($post->category)
                                                <span class="text-secondary font-medium">{{ $post->category->name }}</span>
                                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                            @endif
                                        </div>
                                        <div class="order-3 hidden md:block lg:order-3 lg:w-32 lg:text-right">
                                            <p class="text-sm text-gray-500 py-2">{{ $post->created_at->format(setting('date_format', 'd M Y')) }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-16 border-t border-t-gray-200">
                            <div class="text-center text-gray-400">
                                <p>{{ __('No posts found.') }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Pagination Bottom --}}
                    @if($posts->hasPages())
                        <div class="py-8 px-4 xl:px-8">
                            {{ $posts->links('components.paginate') }}
                        </div>
                    @endif
                </div>

                <div class="xl:col-span-1">
                    <div class="lg:sticky lg:top-14 flex flex-col items-start gap-2 space-y-4 py-10">
                        <button
                            wire:click="filterByCategory(null)"
                            class="group relative text-gray-500 hover:text-gray-900 px-4 xl:px-8 {{ is_null($categoryId) ? 'text-gray-900 font-semibold' : '' }}">
                            {{ __('All') }} <span class="text-sm {{ is_null($categoryId) ? 'text-red-500' : 'text-gray-500' }}">{{ \App\Models\Post::query()->count() }}</span>
                        </button>
                        @foreach($categories as $category)
                            <button
                                wire:click="filterByCategory({{ $category->id }})"
                                class="group relative text-gray-500 hover:text-gray-900 px-4 xl:px-8 {{ $category->id === $categoryId ? 'text-gray-900 font-semibold' : '' }}"
                            >
                                {{ $category->name }} <span class="text-sm {{ $category->id === $categoryId ? 'text-red-500' : 'text-gray-500' }}">{{ $category->posts->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
