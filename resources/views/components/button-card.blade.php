@props([
    'href' => null,
])

<div
    class="flex h-11 items-stretch bg-gray-200 text-sm font-medium text-gray-800 transition-all duration-300 ease-out group-hover:bg-white overflow-hidden">
    @isset($price)
        <div class="inline-grid min-w-25 place-items-center border-r border-r-gray-300">
            {{ $price }}
        </div>
    @endisset
    <a href="{{ $href }}"
       class="relative flex grow items-center justify-between gap-3 px-4 min-w-0"
      >
        <div class="truncate min-w-0">
            {{ $slot }}
        </div>
        <div class="transition duration-300 ease-out group-hover:translate-x-1">
            <x-heroicon-o-arrow-right class="w-5 h-5"/>
        </div>
    </a>
</div>
