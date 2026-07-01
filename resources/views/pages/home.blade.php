<x-layouts.app :seoData="$seoData">
    <div class="bg-white" data-page="home">
        @foreach($resolvedBlocks as $block)
            <x-dynamic-component
                :component="'sections.builder.' . $block['type']"
                :data="$block['data'] ?? []"
                :items="$block['items'] ?? []"
            />
        @endforeach
    </div>
</x-layouts.app>
