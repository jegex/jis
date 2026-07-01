@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $heroImage = $data['image'] ?? 'images/hero.png';
    $badgeEnabled = $data['badge_enabled'] ?? true;
    $badge = $data['badge'] ?? null;
    $title = $data['title'] ?? null;
    $subtitle = $data['subtitle'] ?? null;
    $btnPrimaryLabel = $data['primary_button_label'] ?? null;
    $btnPrimaryUrl = $data['primary_button_url'] ?? '#services';
    $btnSecondaryLabel = $data['secondary_button_label'] ?? null;
    $btnSecondaryUrl = $data['secondary_button_url'] ?? '#contact';
@endphp

<section class="bg-white flex items-center overflow-hidden border-b border-b-gray-200" data-hero>
    <div class="container relative flex items-center w-full">
        @if($heroImage)
        <div class="absolute inset-y-0 hidden lg:block right-0 w-[70%] lg:w-[55%] pointer-events-none select-none">
            <div class="absolute inset-0 bg-gradient-to-l from-transparent via-white/30 to-white"></div>
            <img src="{{ asset($heroImage) }}" alt="" aria-hidden="true" class="w-full h-full object-contain object-right scale-110">
        </div>
        @endif
        <div class="relative z-10 max-w-2xl py-24">
            @if($badgeEnabled && $badge)
            <div data-hero="badge">
                <span class="inline-flex items-center gap-2 bg-primary-light text-primary/80 text-sm px-4 py-1.5 rounded-full border border-primary/10 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                    {{ locale_text($badge, $locale) }}
                </span>
            </div>
            @endif
            @if($title)
            <h1 data-hero="title" class="text-4xl sm:text-5xl md:text-6xl tracking-tighter text-gray-900 leading-tighter mt-6">
                {{ locale_text($title, $locale) }}
            </h1>
            @endif
            @if($subtitle)
            <p data-hero="subtitle" class="mt-6 text-lg md:text-xl text-gray-500 max-w-xl tracking-tight leading-relaxed">
                {{ locale_text($subtitle, $locale) }}
            </p>
            @endif
            @if($btnPrimaryLabel || $btnSecondaryLabel)
            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row md:items-start" data-hero-stagger>
                @if($btnPrimaryLabel)
                <a href="{{ $btnPrimaryUrl }}" data-hero-btn="primary" class="relative select-none group h-12 inline-flex items-center gap-3 rounded-lg bg-primary pl-6 pr-2 overflow-hidden text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-shadow hover:shadow-xl hover:shadow-primary-500/30 active:scale-[0.97]">
                    <span class="absolute inset-y-1 right-1 w-11 rounded-sm bg-secondary" data-hero-fill></span>
                    <span class="relative z-10 text-white" data-hero-label>{{ locale_text($btnPrimaryLabel, $locale) }}</span>
                    <span class="relative z-10 text-white inline-flex items-center justify-center size-9">
                        <x-heroicon-o-arrow-right stroke-width="2" class="size-4 absolute" data-hero-icon="exit" />
                        <x-heroicon-o-arrow-right stroke-width="2" class="size-4 absolute" data-hero-icon="enter" />
                    </span>
                </a>
                @endif
                @if($btnSecondaryLabel)
                <a href="{{ $btnSecondaryUrl }}" class="h-12 select-none inline-flex text-gray-900 bg-gray-100 hover:bg-gray-200 items-center gap-2 rounded-lg border border-gray-300 px-6 py-3.5 text-sm font-semibold active:scale-[0.97]">
                    <span data-hero-label>{{ locale_text($btnSecondaryLabel, $locale) }}</span>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</section>
