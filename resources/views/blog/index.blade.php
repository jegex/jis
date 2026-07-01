<x-layouts.app>

    <div class="bg-white">
        <section class="border-b border-b-gray-200">
            <div class="container border-x border-x-gray-200 py-4 xl:px-8!">
                <h1 class="text-xl font-medium text-gray-900 dark:text-gray-100">{{ __('Blog') }}</h1>
            </div>
        </section>

        @if($posts->isNotEmpty())
            <section class="border-b border-b-gray-200">
                <div class="container border-x border-x-gray-200 xl:px-0!">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-x divide-gray-200">
                        <div class="">
                            <a href="{{ route('blog.show', $posts->first()) }}" class="group relative">
                                @if($posts->first()->getFirstMediaUrl('featured_image'))
                                    <div class="relative aspect-video overflow-hidden">
                                        <img
                                            src="{{ $posts->first()->getFirstMediaUrl('featured_image', 'thumb') }}"
                                            alt="{{ $posts->first()->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                    </div>
                                @endif
                                <div class="py-4 px-4 lg:px-8">
                                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary">
                                        {{ $posts->first()->title }}
                                    </h3>
                                    <p class="text-sm text-gray-500 py-2">
                                        {{ $posts->first()->created_at->format('d M Y') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $posts->first()->excerpt }}
                                    </p>
                                </div>
                            </a>
                        </div>
                        <div class="flex flex-col grow divide-y divide-gray-200">
                            @foreach($posts->take(5) as $post)
                                @if($post->isNot($posts->first()))
                                    <a href="{{ route('blog.show', $post) }}" class="group py-4 px-4 lg:px-8 hover:bg-gray-50">
                                        <h3 class="font-semibold text-gray-900 group-hover:text-primary">{{ $post->title }}</h3>
                                        <p class="text-sm text-gray-500 py-2">{{ $post->created_at->format('d M Y') }}</p>
                                        <p class="text-sm text-gray-500">{{ $post->excerpt }}</p>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="border-b border-b-gray-200">
            <div class="container h-10 border-x border-x-gray-200">
                {{-- Filter space --}}
            </div>
        </div>

        <section>
            <div class="container border-x border-x-gray-200 xl:px-0!">
                <div class="grid grid-cols-1 xl:grid-cols-3 divide-x divide-gray-200">
                    <div class="xl:col-span-2 py-10">
                        @if($posts->isNotEmpty())
                            <div class="flex flex-col" data-stagger>
                                @foreach($posts as $post)
                                    <a href="{{ route('blog.show', $post) }}" class="group relative z-10 flex cursor-pointer flex-col gap-2 border-b border-transparent px-4 py-3 transition-colors hover:border-gray-200 hover:bg-gray-50 md:py-6 lg:flex-row lg:gap-8 xl:px-8">
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
                                            <p class="text-sm text-gray-500 py-2">{{ $post->created_at->format('d M Y') }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-16 text-gray-400">
                                <p>{{ __('No posts yet.') }}</p>
                            </div>
                        @endif

                        @if(method_exists($posts, 'links'))
                            <div class="mt-12 px-4 lg:px-8">
                                {{ $posts->links() }}
                            </div>
                        @endif
                    </div>
                    <div class="xl:col-span-1">
                        <div class="lg:sticky lg:top-24 flex flex-col gap-2 space-y-4 py-10">
                            @foreach($categories as $category)
                                <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="group relative text-gray-500 hover:text-primary px-4 xl:px-8">
                                    {{ $category->name }} <span class="text-sm text-gray-500">{{ $category->posts->count() }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</x-layouts.app>
