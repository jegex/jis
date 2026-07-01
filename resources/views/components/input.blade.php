@props([
    'type' => 'text',
    'label' => '',
    'name' => '',
    'id' => '',
    'placeholder' => '',
    'size' => 'md',
    'variant' => 'default',
    'color' => null,
    'icon' => null,
    'iconPosition' => 'before',
    'pill' => false,
    'floating' => false,
    'helperText' => '',
    'error' => false,
    'success' => false,
])

@php
    $variant = match ($variant) {
        'flat' => 'default',
        'outlined' => 'default',
        'ghost' => 'underlined',
        default => $variant,
    };

    $inputId = $id ?: $name;

    $baseClass = 'block w-full sm:text-sm disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden';

    $sizeClass = match ($size) {
        'sm' => 'py-1.5 sm:py-2 px-3',
        'md' => 'py-2 sm:py-2 px-4',
        'lg' => 'p-3.5 sm:p-5',
        default => 'py-2.5 sm:py-3 px-4',
    };

    $shapeClass = $pill ? 'rounded-full' : 'rounded-lg';

    $bgTextClass = match ($variant) {
        'default' => 'bg-layer text-foreground placeholder:text-muted-foreground-1',
        'stone' => 'bg-surface text-foreground placeholder:text-muted-foreground-1 focus:bg-layer',
        'underlined' => 'bg-transparent text-foreground placeholder:text-muted-foreground-1',
        default => 'bg-layer text-foreground placeholder:text-muted-foreground-1',
    };

    if ($error) {
        $borderClass = $variant === 'underlined' ? 'border-0 border-b-2 border-b-red-500' : 'border border-red-500';
        $focusClass = $variant === 'underlined' ? 'focus:border-b-red-500 focus:ring-0' : 'focus:border-red-500 focus:ring-red-500';
    } elseif ($success) {
        $borderClass = $variant === 'underlined' ? 'border-0 border-b-2 border-b-teal-500' : 'border border-teal-500';
        $focusClass = $variant === 'underlined' ? 'focus:border-b-teal-500 focus:ring-0' : 'focus:border-teal-500 focus:ring-teal-500';
    } elseif ($variant === 'underlined') {
        $borderClass = 'border-0 border-b-2 border-b-line-2';
        $focusClass = 'focus:border-b-primary-focus focus:ring-0';
    } elseif ($variant === 'stone') {
        $borderClass = 'border border-transparent';
        $focusClass = 'focus:border-primary-focus focus:ring-primary-focus';
    } else {
        $borderClass = 'border border-layer-line';
        $focusClass = 'focus:border-primary-focus focus:ring-primary-focus';
    }

    $variantClasses = "{$bgTextClass} {$borderClass} {$focusClass}";

    if ($floating) {
        $sizeClass = 'p-4';
        $floatingClasses = 'placeholder:text-transparent focus:pt-6 focus:pb-2 not-placeholder-shown:pt-6 not-placeholder-shown:pb-2 autofill:pt-6 autofill:pb-2';
    } else {
        $floatingClasses = '';
    }

    $iconPadding = '';
    if ($icon && ! $floating) {
        $iconPadding = $iconPosition === 'before'
            ? ($variant === 'underlined' ? 'ps-8' : 'ps-10')
            : ($variant === 'underlined' ? 'pe-8' : 'pe-10');
    }

    $validationPadding = ($error || $success) && ! $floating ? 'pe-10' : '';

    $peerClass = $floating ? 'peer' : '';

    $classes = trim("{$baseClass} {$sizeClass} {$variantClasses} {$shapeClass} {$iconPadding} {$validationPadding} {$floatingClasses} {$peerClass}");
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label && ! $floating)
        <label for="{{ $inputId }}" class="block mb-2 text-sm font-medium text-foreground">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        @if ($icon && $iconPosition === 'before' && ! $floating)
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <x-dynamic-component component="{{ $icon }}" class="size-4 text-muted-foreground-1" />
            </div>
        @endif

        <input
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" @endif
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            {{ $attributes->except('class')->class($classes) }}
        >

        @if ($icon && $iconPosition === 'after' && ! $floating)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                <x-dynamic-component component="{{ $icon }}" class="size-4 text-muted-foreground-1" />
            </div>
        @endif

        @if ($error && ! $floating)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                <x-heroicon-o-exclamation-circle class="size-4 text-red-500" />
            </div>
        @elseif ($success && ! $floating)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                <x-heroicon-o-check-circle class="size-4 text-teal-500" />
            </div>
        @endif

        @if ($floating && $label)
            <label for="{{ $inputId }}" class="absolute top-0 start-0 p-4 h-full sm:text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent text-foreground origin-top-left peer-disabled:opacity-50 peer-disabled:pointer-events-none
                peer-focus:scale-90
                peer-focus:translate-x-0.5
                peer-focus:-translate-y-1.5
                peer-focus:text-muted-foreground-1
                peer-not-placeholder-shown:scale-90
                peer-not-placeholder-shown:translate-x-0.5
                peer-not-placeholder-shown:-translate-y-1.5
                peer-not-placeholder-shown:text-muted-foreground-1">
                {{ $label }}
            </label>
        @endif
    </div>

    @if ($error)
        @error($name)
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
        @enderror
    @elseif ($helperText)
        <p class="mt-2 text-sm {{ $success ? 'text-teal-500' : 'text-muted-foreground-1' }}">
            {{ $helperText }}
        </p>
    @else
        @error($name)
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
        @enderror
    @endif
</div>
