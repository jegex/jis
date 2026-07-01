@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $stats = $data['items'] ?? [];
    $label = $data['label'] ?? null;
    $title = $data['title'] ?? null;
    $description = $data['description'] ?? null;
@endphp

<section class="relative z-10 border-b border-b-gray-200">
    <div class="container border-x border-x-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-px overflow-hidden divide-x divide-gray-200">
            @foreach($stats as $stat)
                <div class="flex p-6 md:p-8 gap-4 items-center">
                    @svg('heroicon-o-' . ($stat['icon'] ?? 'film'), 'w-8 h-8 text-primary')
                    <div>
                        <div class="text-3xl md:text-4xl font-bold text-gray-900" data-counter="{{ $stat['value'] }}" data-suffix="{{ $stat['suffix'] ?? '' }}">0</div>
                        <div class="mt-1 text-sm text-gray-500 font-medium">{{ locale_text($stat['label'] ?? null, $locale) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
