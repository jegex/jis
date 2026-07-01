@props([
    'label' => null,
    'title' => null,
    'description' => null,
    'align' => 'left',
    'actionText' => null,
    'actionUrl' => null,
])

<div {{ $attributes }}>
    <div @class([
        'flex flex-wrap gap-4 mb-10',
        'text-center justify-center' => $align === 'center',
        'items-end justify-between' => $align === 'left',
    ])>
        <div data-animate="fade-up" @class(['max-w-2xl mx-auto' => $align === 'center'])>
            @if($label)
                <span class="inline-block text-secondary font-semibold text-xs tracking-widest uppercase mb-3">{{ $label }}</span>
            @endif
            @if($title)
                <h2 class="text-3xl tracking-tighter ">{{ $title }}</h2>
            @endif
            @if($description)
                <p class="mt-3 text-gray-500">{{ $description }}</p>
            @endif
        </div>
        @if($actionText && $actionUrl && $align === 'left')
            <x-button
                :href="$actionUrl"
                variant="outlined"
                size="xs"
                icon="heroicon-o-chevron-right"
                iconPosition="after"
                color="gray"
            >
                {{ $actionText }}
            </x-button>
        @endif
    </div>


    {{ $slot }}

</div>
