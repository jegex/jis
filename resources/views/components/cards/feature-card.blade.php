@props([
    'icon' => 'photo',
    'title' => null,
    'description' => null,
])

<div class="group relative overflow-hidden bg-gray-100 hover:bg-primary-50 transition-colors" data-stagger-item>
    <div class="p-5 space-y-2">
        <x-dynamic-component
            :component="'heroicon-o-' . $icon"
            class="w-10 h-10 text-primary"
            data-draw-svg
            data-draw-scroll
            data-draw-delay="0.75"
            data-draw-duration="0.75"
            data-draw-initial="80%"
            data-draw-loop/>
        <h3 class="text-md text-gray-900">{{ $title }}</h3>
        <p class="text-sm text-gray-500">{{ $description }}</p>
    </div>
</div>
