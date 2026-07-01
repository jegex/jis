@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $partners = $data['items'] ?? [];
    $label = $data['label'] ?? null;
    $title = $data['title'] ?? null;
    $description = $data['description'] ?? null;
@endphp

<section class="bg-gray-50 border-b border-b-gray-200">
    <div class="container py-16 border-x border-x-gray-200">
        <x-sections.section-header
            :label="locale_text($label, $locale)"
            :title="locale_text($title, $locale)"
            :description="locale_text($description, $locale)"
        />

        @if($partners)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 items-center" data-stagger>
                @foreach($partners as $item)
                    <div class="flex justify-center" data-stagger-item>
                        @if(!empty($item['url']))
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="opacity-50 hover:opacity-100 transition-opacity">
                        @endif
                        @if(!empty($item['logo']))
                            <img src="{{ asset($item['logo']) }}" alt="{{ $item['name'] ?? '' }}" class="h-12 object-contain" />
                        @else
                            <span class="text-gray-400 font-semibold text-lg">{{ $item['name'] ?? '' }}</span>
                        @endif
                        @if(!empty($item['url']))
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
