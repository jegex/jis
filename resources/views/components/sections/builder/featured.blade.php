@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $sectionLabel = $data['label'] ?? null;
    $sectionTitle = $data['title'] ?? null;
    $sectionDescription = $data['description'] ?? null;
    $services = collect($data['services']) ?? [];
@endphp

<section id="featured" class="border-b border-b-gray-200">
    <div class="container py-16 border-x border-x-gray-200">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2 flex mb-10 justify-between">
                <x-sections.section-header
                    :label="$sectionLabel ? locale_text($sectionLabel, $locale) : null"
                    :title="$sectionTitle ? locale_text($sectionTitle, $locale) : null"
                    :description="$sectionDescription ? locale_text($sectionDescription, $locale) : null"
                />
            </div>

            @if($services)
            <div class="lg:col-span-2 grid grid-cols-2 gap-4 overflow-hidden" data-stagger>
                @foreach($services->shift(2) as $item)
                    <x-cards.feature-card
                        :icon="$item['icon'] ?? 'light-bulb'"
                        :title="locale_text($item['title'] ?? null, $locale)"
                        :description="locale_text($item['description'] ?? null, $locale)"
                    />
                @endforeach
            </div>
            @endif

            @if($services)
            <div class="lg:col-span-full grid grid-cols-2 gap-4 lg:grid-cols-4 overflow-hidden" data-stagger>
                @foreach($services->all() as $item)
                    <x-cards.feature-card
                        :icon="$item['icon'] ?? 'photo'"
                        :title="locale_text($item['title'] ?? null, $locale)"
                        :description="locale_text($item['description'] ?? null, $locale)"
                    />
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>
