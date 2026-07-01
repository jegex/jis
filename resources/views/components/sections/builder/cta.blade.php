@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $ctaLabel = $data['label'] ?? null;
    $ctaTitle = $data['title'] ?? null;
    $ctaBtnLabel = $data['button_label'] ?? null;
    $ctaBtnUrl = $data['button_url'] ?? '#contact';
@endphp

<section data-stagger>
    <div class="relative container py-16 border-x border-x-gray-200 overflow-hidden bg-linear-to-br from-white via-primary/20 to-white">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="relative">
                <img src="{{ asset('images/bgcta.png') }}" data-animate="fade-left" class="hidden lg:block size-full overflow-hidden object-cover bg-center object-center" alt="ship design">
                <div class="absolute inset-0 bg-linear-to-r from-primary-dark to-transparent"></div>
            </div>
        </div>
        <x-sections.section-header
            :label="$ctaLabel ? locale_text($ctaLabel, $locale) : null"
            :title="$ctaTitle ? locale_text($ctaTitle, $locale) : null"
            class="relative lg:text-white w-full lg:w-1/3">
            @if($ctaBtnLabel)
            <x-button
                :href="$ctaBtnUrl"
                color="gray"
                size="lg"
                icon="heroicon-o-arrow-right"
                icon-position="after">
                {{ locale_text($ctaBtnLabel, $locale) }}
            </x-button>
            @endif
        </x-sections.section-header>
    </div>
</section>
