<x-layouts.app :model="$model ?? $post">
    @php $readTime = ceil(str_word_count(strip_tags($post->content)) / 200); @endphp

    <div class="bg-white">
        <section class="border-b border-b-gray-200">
            <div class="container border-x border-x-gray-200 py-4 xl:px-8!">
                <nav class="flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('home') }}" class="hover:text-primary transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <a href="{{ route('blog.index') }}" class="hover:text-primary transition-colors">{{ __('Blog') }}</a>
                    @if($post->category)
                        <span>/</span>
                        <span class="text-gray-400">{{ $post->category->name }}</span>
                    @endif
                </nav>
            </div>
        </section>

        <section class="border-b border-b-gray-200">
            <div class="container border-x border-x-gray-200 py-6 xl:px-8!">
                <h1 class="text-xl font-medium text-gray-900">{{ $post->title }}</h1>
            </div>
        </section>

        <section>
            <div class="container border-x border-x-gray-200 xl:px-0!">
                <div class="lg:grid lg:grid-cols-5 divide-x divide-gray-200">

                    {{-- Main Content --}}
                    <div class="lg:col-span-3 py-8 px-4 lg:px-8 space-y-6">
                        @if($post->getFirstMediaUrl('featured_image'))
                            <div class="overflow-hidden rounded-xl bg-gray-100" data-animate="fade-up">
                                <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full">
                            </div>
                        @endif

                        @if($post->excerpt)
                            <div data-animate="fade-up" data-delay="0.1">
                                <p class="text-gray-500 leading-relaxed">{{ $post->excerpt }}</p>
                            </div>
                        @endif

                        <div data-animate="fade-up" data-delay="0.15" class="prose max-w-none break-words prose-headings:text-gray-900 prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm">
                            {!! $post->content !!}
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-full bg-primary-light text-primary font-semibold text-sm">
                                        {{ strtoupper(substr($post->author?->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $post->author?->name }}</p>
                                        <p class="text-xs text-gray-400">{{ __('Published on') }} {{ $post->published_at?->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-400">
                                    <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-clock class="w-4 h-4" />
                                        {{ $readTime }} {{ __('min read') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-2 py-8 px-4 lg:px-8">
                        <div class="lg:sticky lg:top-24 space-y-6">
                        @if($post->category)
                            <div>
                                <span class="inline-block text-secondary font-semibold text-xs tracking-widest uppercase">{{ $post->category->name }}</span>
                            </div>
                        @endif

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 shrink-0" />
                                <span>{{ $post->published_at?->format('F d, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400 shrink-0" />
                                <span>{{ $readTime }} {{ __('min read') }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400 shrink-0" />
                                <span>{{ $post->author?->name }}</span>
                            </div>
                        </div>

                        @if($post->tags->isNotEmpty())
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-xs font-semibold text-gray-400 tracking-widest uppercase mb-3">{{ __('Tags') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($post->tags as $tag)
                                        <span class="px-3 py-1 bg-primary-light text-primary rounded-full text-sm font-medium">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($recentPosts->isNotEmpty())
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-xs font-semibold text-gray-400 tracking-widest uppercase mb-4">{{ __('Latest Posts') }}</h3>
                                <div class="flex flex-col gap-4">
                                    @foreach($recentPosts as $recent)
                                        <a href="{{ route('blog.show', $recent) }}" class="group">
                                            <p class="text-sm font-medium text-gray-900 group-hover:text-primary transition-colors leading-snug">{{ $recent->title }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $recent->published_at?->format('d M Y') }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-6">
                            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:text-primary-dark transition-colors">
                                <x-heroicon-o-arrow-left class="w-4 h-4" />
                                {{ __('Back to Blog') }}
                            </a>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </section>

        @if($relatedPosts->isNotEmpty())
            <section class="border-t border-t-gray-200">
                <div class="container py-12 border-x border-x-gray-200 xl:px-8!">
                    <x-sections.section-header
                        label="{{ __('Blog') }}"
                        title="{{ __('Keep Reading') }}"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-stagger>
                        @foreach($relatedPosts as $related)
                            <x-cards.content-card :item="$related" mediaCollection="featured_image" titleRoute="blog.show" buttonRoute="blog.show" :showMeta="true" data-stagger-item />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
