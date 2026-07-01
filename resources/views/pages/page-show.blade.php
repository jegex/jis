<x-layouts.app :model="$model ?? $page">
    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 py-3 xl:px-8!">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">{{ __('Home') }}</a>
                <span>/</span>
                <span class="text-gray-400">{{ $page->title }}</span>
            </nav>
        </div>
    </section>

    <section class="border-b border-b-gray-200">
        <div class="container border-x border-x-gray-200 xl:px-8!">
            <div class="max-w-3xl mx-auto py-12 md:py-16">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-display leading-tight mb-8" data-animate="fade-up">{{ $page->title }}</h1>

                <div class="prose max-w-none break-words prose-headings:font-display prose-headings:text-gray-900 prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm" data-animate="fade-up" data-delay="0.1">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
