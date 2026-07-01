@props([
    'href' => null,
    'size' => 'md',
    'variant' => 'solid',
    'color' => 'primary',
    'icon' => null,
    'iconPosition' => 'before',
    'pill' => false,
    'loading' => false,
    'fullWidth' => false,
])

@php
    $variant = match ($variant) {
        'flat' => 'soft',
        'outlined' => 'outline',
        default => $variant,
    };

    $rootClass = 'cursor-pointer select-none disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden';

    if ($variant === 'link') {
        $variantClass = 'inline-flex items-center gap-2 text-sm font-semibold';
    } else {
        $variantClass = 'inline-flex items-center justify-center gap-2 text-sm font-medium rounded-lg';
    }

    $sizeClass = match ($size) {
        'xs' => 'py-1.5 px-2.5 text-xs',
        'sm' => 'py-1.5 px-3',
        'lg' => 'py-2.5 px-5 text-lg',
        default => 'py-2 px-4',
    };

    $colorClass = match ($variant) {
        'solid' => match ($color) {
            'primary' => 'bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus active:bg-primary-active',
            'secondary' => 'bg-secondary border border-secondary-line text-secondary-foreground hover:bg-secondary-hover focus:bg-secondary-focus active:bg-secondary-active',
            default => 'bg-gray-500 border border-transparent text-white hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700',
        },
        'outline' => match ($color) {
            'primary' => 'border border-layer-line text-muted-foreground-1 hover:border-primary-hover hover:text-primary-hover focus:border-primary-focus focus:text-primary-focus active:border-primary-active active:text-primary-active',
            'secondary' => 'border border-layer-line text-muted-foreground-1 hover:border-secondary-hover hover:text-secondary-hover focus:border-secondary-focus focus:text-secondary-focus active:border-secondary-active active:text-secondary-active',
            default => 'border border-line-5 text-muted-foreground-1 hover:border-line-8 hover:text-foreground focus:border-line-8 focus:text-foreground active:border-line-8 active:text-foreground',
        },
        'ghost' => match ($color) {
            'primary' => 'border border-transparent text-primary-600 hover:bg-primary-100 hover:text-primary-800 focus:bg-primary-100 focus:text-primary-800 active:bg-primary-100 active:text-primary-800 dark:text-primary-500 dark:hover:bg-primary-500/20 dark:hover:text-primary-400 dark:focus:bg-primary-500/20 dark:focus:text-primary-400 dark:active:bg-primary-500/20 dark:active:text-primary-400',
            'secondary' => 'border border-transparent text-secondary hover:bg-secondary/10 focus:bg-secondary/10 active:bg-secondary/10',
            default => 'border border-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-800 focus:bg-gray-100 focus:text-gray-800 active:bg-gray-100 active:text-gray-800',
        },
        'soft' => match ($color) {
            'primary' => 'border border-transparent bg-primary-100 text-primary-800 hover:bg-primary-200 focus:bg-primary-200 active:bg-primary-200 dark:bg-primary-500/20 dark:text-primary-400 dark:hover:bg-primary-500/30 dark:focus:bg-primary-500/30 dark:active:bg-primary-500/30',
            'secondary' => 'border border-transparent bg-secondary/10 text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/20',
            default => 'border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200',
        },
        'white' => match ($color) {
            'primary' => 'border border-layer-line bg-layer text-primary shadow-2xs hover:bg-layer-hover focus:bg-layer-focus active:bg-layer-active',
            'secondary' => 'border border-layer-line bg-layer text-secondary shadow-2xs hover:bg-layer-hover focus:bg-layer-focus active:bg-layer-active',
            default => 'border border-layer-line bg-layer text-layer-foreground shadow-2xs hover:bg-layer-hover focus:bg-layer-focus active:bg-layer-active',
        },
        'link' => match ($color) {
            'primary' => 'text-primary hover:text-primary-hover focus:text-primary-focus active:text-primary-active',
            'secondary' => 'text-secondary hover:text-secondary-hover focus:text-secondary-focus active:text-secondary-active',
            default => 'text-muted-foreground-1 hover:text-primary-hover focus:text-primary-focus active:text-primary-active',
        },
        default => 'bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus active:bg-primary-active',
    };

    if ($pill && $variant !== 'link') {
        $variantClass = str_replace('rounded-lg', 'rounded-full', $variantClass);
    }

    if ($fullWidth) {
        $variantClass .= ' w-full';
    }

    $iconSize = match ($size) {
        'xs' => 'size-3.5',
        'lg' => 'size-5',
        default => 'size-4',
    };
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => "{$rootClass} {$variantClass} {$colorClass} {$sizeClass}"]) }}
        @if ($loading) aria-disabled="true" aria-busy="true" @endif
    >
        @if ($loading)
            <span class="animate-spin {{ $iconSize }} border-3 border-current border-t-transparent rounded-full"
                  role="status" aria-label="loading">
                <span class="sr-only">Loading...</span>
            </span>
        @elseif ($icon && $iconPosition === 'before')
            <x-dynamic-component component="{{ $icon }}" class="{{ $iconSize }}"/>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'after' && ! $loading)
            <x-dynamic-component component="{{ $icon }}" class="{{ $iconSize }}"/>
        @endif
    </a>
@else
    <button
        {{ $attributes->merge(['class' => "{$rootClass} {$variantClass} {$colorClass} {$sizeClass}"]) }}
        @if ($loading) disabled aria-busy="true" @endif
    >
        @if ($loading)
            <span class="animate-spin {{ $iconSize }} border-3 border-current border-t-transparent rounded-full"
                  role="status" aria-label="loading">
                <span class="sr-only">Loading...</span>
            </span>
        @elseif ($icon && $iconPosition === 'before')
            <x-dynamic-component component="{{ $icon }}" class="{{ $iconSize }}"/>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'after' && ! $loading)
            <x-dynamic-component component="{{ $icon }}" class="{{ $iconSize }}"/>
        @endif
    </button>
@endif
