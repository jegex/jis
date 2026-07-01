@props(['images' => []])

<div
    x-data="{
        images: {{ Js::from($images) }},
        activeIndex: 0,
        showLightbox: false,
        touchStartX: 0,

        init() {
            if (this.images.length === 0) return;
            this.$watch('activeIndex', (index) => window.dispatchEvent(new CustomEvent('gallery-swap', { detail: { index } })));
        },

        selectImage(index) {
            this.activeIndex = index;
        },

        prev() {
            this.activeIndex = this.activeIndex > 0 ? this.activeIndex - 1 : this.images.length - 1;
        },

        next() {
            this.activeIndex = this.activeIndex < this.images.length - 1 ? this.activeIndex + 1 : 0;
        },

        openLightbox(index) {
            this.activeIndex = index;
            this.showLightbox = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.lightbox?.focus());
        },

        closeLightbox() {
            this.showLightbox = false;
            document.body.style.overflow = '';
        },

        handleTouchStart(e) {
            this.touchStartX = e.touches[0].clientX;
        },

        handleTouchEnd(e) {
            const diff = this.touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? this.next() : this.prev();
            }
        },
    }"
    x-init="init"
    data-gallery
>
    {{-- Main display area --}}
    <div class="relative overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800 cursor-pointer group aspect-video"
         @click="openLightbox(activeIndex)">

        <div class="relative h-full">
            <img
                x-ref="mainImage"
                :src="images.length > 0 ? images[activeIndex].url : ''"
                :alt="images.length > 0 ? images[activeIndex].alt : ''"
                class="w-full h-full object-contain"
            >
        </div>

        {{-- Prev/Next arrows on main image --}}
        <button
            x-show="images.length > 1"
            @click.stop="prev()"
            class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-white/80 dark:bg-gray-900/80 text-gray-800 dark:text-gray-200 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-white dark:hover:bg-gray-900"
            aria-label="{{ __('Previous image') }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button
            x-show="images.length > 1"
            @click.stop="next()"
            class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-white/80 dark:bg-gray-900/80 text-gray-800 dark:text-gray-200 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-white dark:hover:bg-gray-900"
            aria-label="{{ __('Next image') }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Thumbnails row --}}
    <template x-if="images.length > 1">
        <div class="mt-4 flex gap-3 overflow-x-auto p-2.5" x-ref="thumbnails">
            <template x-for="(image, index) in images" :key="index">
                <button
                    @click="selectImage(index)"
                    :class="{
                        'ring-2 ring-primary scale-105': activeIndex === index,
                        'opacity-60 hover:opacity-100': activeIndex !== index,
                    }"
                    class="flex-shrink-0 size-20 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <img :src="image.url_small" :alt="image.alt" class="w-full h-full object-cover">
                </button>
            </template>
        </div>
    </template>

    {{-- Lightbox overlay --}}
    <template x-teleport="body">
        <div
            x-ref="lightbox"
            x-show="showLightbox"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-overlay/90 backdrop-blur-sm"
            @click.self="closeLightbox()"
            @keydown.escape="closeLightbox()"
            @keydown.left="prev()"
            @keydown.right="next()"
            x-cloak
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('Image gallery') }}"
            tabindex="-1"
        >
            {{-- Image container --}}
            <div
                class="relative max-w-[90vw] max-h-[90vh] flex items-center justify-center"
                x-show="showLightbox"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="scale-100 opacity-100"
                x-transition:leave-end="scale-95 opacity-0"
                @touchstart="handleTouchStart($event)"
                @touchend="handleTouchEnd($event)"
            >
                <img
                    :src="images.length > 0 ? images[activeIndex].url : ''"
                    :alt="images.length > 0 ? images[activeIndex].alt : ''"
                    class="max-h-[90vh] max-w-[90vw] object-contain select-none"
                >
            </div>

            {{-- Top bar: counter + close --}}
            <div class="absolute top-4 left-1/2 -translate-x-1/2 flex items-center gap-4 px-4 py-2 rounded-full bg-gray-900/60 text-white text-sm">
                <span x-text="`${activeIndex + 1} / ${images.length}`"></span>
            </div>

            <button
                @click="closeLightbox()"
                class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-gray-900/60 text-white hover:bg-gray-900/80 transition-colors"
                aria-label="{{ __('Close gallery') }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Navigation arrows --}}
            <button
                x-show="images.length > 1"
                @click="prev()"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full bg-gray-900/60 text-white hover:bg-gray-900/80 transition-colors"
                aria-label="{{ __('Previous image') }}"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button
                x-show="images.length > 1"
                @click="next()"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full bg-gray-900/60 text-white hover:bg-gray-900/80 transition-colors"
                aria-label="{{ __('Next image') }}"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </template>
</div>
