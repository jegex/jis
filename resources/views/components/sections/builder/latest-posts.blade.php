@props(['data' => [], 'items' => []])

@php
    $posts = $items;
    $locale = app()->getLocale();
    $sectionLabel = $data['label'] ?? null;
    $sectionTitle = $data['title'] ?? null;
    $sectionDescription = $data['description'] ?? null;
    $showViewAll = $data['show_view_all'] ?? true;
@endphp

@if($posts->isNotEmpty())
    <section class="bg-gray-50 border-b border-b-gray-200">
        <div class="container py-16 border-x border-x-gray-200">
            <x-sections.section-header
                :label="locale_text($sectionLabel, $locale)"
                :title="locale_text($sectionTitle, $locale)"
                :description="locale_text($sectionDescription, $locale)"
                :actionText="$showViewAll ? __('View All Posts') : null"
                :actionUrl="$showViewAll ? route('blog.index') : null"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <x-cards.content-card :item="$post" mediaCollection="featured_image" titleRoute="blog.show" buttonRoute="blog.show" :showMeta="true" />
                @endforeach
            </div>
        </div>
    </section>
@endif
