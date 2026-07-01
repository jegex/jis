@php
    $isNested = $isNested ?? false;
    $translations = json_decode($item->name, true) ?? [];
    $locale = app()->getLocale();
    $fallback = app()->getFallbackLocale();
    $displayName = $translations[$locale] ?? $translations[$fallback] ?? $item->name;
@endphp

@if($isNested)
    <li>
        <div class="relative group">
            <a
                target="{{ $item->target }}"
                class="flex items-center justify-between px-4 py-1.5 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ $item->link_class }}"
                href="{{ $item->link }}"

            >
                {{ $displayName }}
                @if(! $item->children->isEmpty())
                    <span class="ml-1">
                        <x-heroicon-o-chevron-right class="w-3 h-3" />
                    </span>
                @endif
            </a>
            @if(! $item->children->isEmpty())
                    <ul class="menu-dropdown absolute left-full top-0 bg-white rounded-md shadow-lg py-2 min-w-max z-50 space-y-1">
                    @foreach($item->children as $child)
                        @include('components.menu.header-item', ['item' => $child, 'isNested' => true])
                    @endforeach
                </ul>
            @endif
        </div>
    </li>
@else
    <li class="group relative {{ $item->wrapper_class }}">
        <a
            target="{{ $item->target }}"
            class="inline-flex items-center text-white/80 hover:text-white px-3 py-2 gap-1 rounded-md text-sm font-medium {{ $item->link_class }}"
            href="{{ $item->link }}"

        >
            {{ $displayName }}
            @if(! $item->children->isEmpty())
                <span class="ml-1">
                    <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 group-hover:rotate-180" />
                </span>
            @endif
        </a>
        @if(! $item->children->isEmpty())
            <ul class="menu-dropdown absolute left-0 top-full bg-white rounded-md shadow-lg py-2 min-w-max z-50 space-y-1">
                @foreach($item->children as $child)
                    @include('components.menu.header-item', ['item' => $child, 'isNested' => true])
                @endforeach
            </ul>
        @endif
    </li>
@endif
