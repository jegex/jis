@php
    $color = $attributes->get('color', 'gray');
    $colors = [
        'success' => 'bg-success-light text-success',
        'warning' => 'bg-warning-light text-warning',
        'danger' => 'bg-danger-light text-danger',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
    $class = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-1 text-xs rounded-full {$class}"]) }}>
    {{ $slot }}
</span>
