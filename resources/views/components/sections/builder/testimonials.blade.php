@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $testimonials = $data['items'] ?? [];
    $label = $data['label'] ?? null;
    $title = $data['title'] ?? null;
    $description = $data['description'] ?? null;
@endphp

<section class="border-b border-b-gray-200">
    <div class="container py-16 border-x border-x-gray-200">
        <x-sections.section-header
            :label="locale_text($label, $locale)"
            :title="locale_text($title, $locale)"
            :description="locale_text($description, $locale)"
        />

        @if($testimonials)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-stagger>
                @foreach($testimonials as $item)
                    <div class="relative bg-white border border-gray-200 rounded-xl p-6 flex flex-col" data-stagger-item>
                        <div class="mb-4">
                            @php $rating = (int) ($item['rating'] ?? 5); @endphp
                            <div class="flex gap-0.5">
                                @for($i = 0; $i < 5; $i++)
                                    <x-heroicon-s-star class="w-4 h-4 {{ $i < $rating ? 'text-amber-400' : 'text-gray-200' }}" />
                                @endfor
                            </div>
                        </div>
                        <blockquote class="flex-1 text-gray-600 text-sm leading-relaxed">
                            &ldquo;{{ locale_text($item['quote'] ?? null, $locale) }}&rdquo;
                        </blockquote>
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <div class="font-semibold text-gray-900 text-sm">{{ $item['name'] ?? '' }}</div>
                            @php $role = $item['role'] ?? null; @endphp
                            @if($role)
                                <div class="text-gray-500 text-xs">{{ locale_text($role, $locale) }}</div>
                            @endif
                            @if(!empty($item['company']))
                                <div class="text-gray-400 text-xs">{{ $item['company'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
