@props(['menuItem', 'depth' => 0])

<li>
    <a
        href="{{ $menuItem->link }}"
        target="{{ $menuItem->target }}"
        class="flex items-center gap-2 text-sm {{ $depth === 0 ? 'text-gray-500' : 'text-gray-400' }} hover:text-primary transition-colors {{ $menuItem->link_class }}"
    >
        <span class="w-1 h-1 rounded-full {{ $depth === 0 ? 'bg-secondary' : 'bg-gray-300' }} shrink-0"></span>
        @php
            $translations = json_decode($menuItem->name, true) ?? [];
            $name = $translations[app()->getLocale()] ?? $translations[app()->getFallbackLocale()] ?? $menuItem->name;
        @endphp
        {{ $name }}
    </a>
    @if(! $menuItem->children->isEmpty())
        <ul class="flex flex-col gap-3 mt-3 ml-4">
            @foreach($menuItem->children as $child)
                <x-menu.menu-item :menuItem="$child" :depth="$depth + 1" />
            @endforeach
        </ul>
    @endif
</li>
