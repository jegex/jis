@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $sectionLabel = $data['label'] ?? null;
    $sectionTitle = $data['title'] ?? null;
    $sectionDescription = $data['description'] ?? null;
    $services = $data['items'] ?? [];
@endphp

<section id="services" class="border-b border-b-gray-200" data-stagger>
    <div class="container py-16 p-0! border-x border-x-gray-200">
        <div class="grid md:grid-cols-5 divide-x divide-gray-200">
            <div class="bg-linear-to-br from-white via-red-100 to-white md:col-span-2 flex flex-col justify-between">
                <x-sections.section-header
                    class="pt-10 px-4 sm:px-6 lg:px-12"
                    :label="$sectionLabel ? locale_text($sectionLabel, $locale) : null"
                    :title="$sectionTitle ? locale_text($sectionTitle, $locale) : null"
                    :description="$sectionDescription ? locale_text($sectionDescription, $locale) : null"
                />

                <div class="relative pl-12 overflow-hidden" data-animate="fade-up">
                    <div class="border-t border-l border-white pl-2 pt-2 rounded-tl-2xl bg-red-200">
                        <img src="{{ asset('images/ship-design.png') }}" class="size-full overflow-hidden rounded-tl-xl object-cover object-left-top" alt="ship design">
                    </div>
                </div>
            </div>

            <div class="flex flex-col justify-between md:col-span-3 divide-y divide-gray-200">
                @foreach($services as $item)
                    <div class="flex grow gap-4 lg:gap-8 py-4 px-4 lg:px-8 hover:bg-gray-50" data-stagger-item>
                        <x-dynamic-component
                            :component="'heroicon-o-' . ($item['icon'] ?? 'cog')"
                            class="w-10 h-10 text-red-500 shrink-0 mt-1"
                            data-draw-svg
                            data-draw-loop
                            data-animate="fade-left"
                        />
                        <div class="min-w-0" data-animate="fade-right">
                            <h3 class="font-semibold text-gray-900">{{ locale_text($item['title'] ?? null, $locale) }}</h3>
                            <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ locale_text($item['description'] ?? null, $locale) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
