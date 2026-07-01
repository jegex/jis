@php
    use Biostate\FilamentMenuBuilder\Models\Menu;

    if (! function_exists('menuItemsToArray')) {
        function menuItemsToArray($items, $locale, $fallback): array
        {
            return $items->map(function ($item) use ($locale, $fallback) {
                $name = $item->name;
                if (is_string($name)) {
                    $decoded = json_decode($name, true);
                    $name = $decoded[$locale] ?? $decoded[$fallback] ?? $item->name;
                } elseif (is_array($name)) {
                    $name = $name[$locale] ?? $name[$fallback] ?? '';
                }

                return [
                    'id' => $item->id,
                    'name' => $name,
                    'link' => $item->link ?? '#',
                    'target' => $item->target ?? '_self',
                    'children' => $item->children->isNotEmpty()
                        ? menuItemsToArray($item->children, $locale, $fallback)
                        : [],
                ];
            })->values()->toArray();
        }
    }

    $mainMenu = Menu::where('slug', 'main-menu')
        ->with(['items' => fn ($q) => $q->defaultOrder()->with('menuable')])
        ->first();

    $menuData = $mainMenu
        ? menuItemsToArray($mainMenu->items->toTree(), app()->getLocale(), app()->getFallbackLocale())
        : [];

    $languages = config('localizer.locale_with_label');
@endphp

<div
    x-data="{
        open: false,
        stack: [{ items: @js($menuData), name: null }],

        get currentLevel() {
            return this.stack.length - 1;
        },

        get parentName() {
            return this.currentLevel > 0 ? this.stack[this.currentLevel].name : null;
        },

        init() {
            this.$watch('open', (value) => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                    setTimeout(() => {
                        this.stack = [{ items: @js($menuData), name: null }];
                    }, 300);
                }
            });
        },

        drillDown(children, name) {
            this.stack.push({ items: children, name });
        },

        goBack() {
            if (this.stack.length > 1) {
                this.stack.pop();
            }
        },

        close() {
            this.open = false;
        },
    }"
    @keydown.window.escape="close"
    class="md:hidden mr-0"
>
    <button
        @click="open = true"
        class="md:hidden flex items-center text-white/80 hover:text-white p-2 rounded-md"
        aria-label="{{ __('Toggle menu') }}"
    >
        <x-heroicon-o-bars-3 class="w-6 h-6" />
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            style="display: none;"
            class="relative z-[99]"
        >
            <div
                x-show="open"
                x-transition.opacity.duration.100ms
                @click="close"
                class="fixed inset-0 bg-black/50"
            ></div>

            <div class="overflow-hidden fixed inset-0">
                <div class="overflow-hidden absolute inset-0">
                    <div class="flex fixed inset-0 max-w-full">
                        <div
                            x-show="open"
                            @click.away="close"
                            x-transition:enter="transform transition ease-in-out duration-300"
                            x-transition:enter-start="translate-x-full"
                            x-transition:enter-end="translate-x-0"
                            x-transition:leave="transform transition ease-in-out duration-300"
                            x-transition:leave-start="translate-x-0"
                            x-transition:leave-end="translate-x-full"
                            class="w-full h-full"
                        >
                            <div class="flex overflow-y-scroll flex-col h-full bg-white border-l shadow-lg border-gray-100/70">
                                <div class="flex items-center justify-between px-5 pb-5 pt-4 shrink-0">
                                    <template x-if="currentLevel > 0">
                                        <button @click="goBack" class="inline-flex items-center gap-1" aria-label="Back">
                                            <x-heroicon-o-arrow-left class="w-4 h-4" />
                                            <span x-text="parentName" class="truncate max-w-[160px] normal-case"></span>
                                        </button>
                                    </template>
                                    <template x-if="currentLevel === 0" >
                                        <a href="{{ route('home') }}" class="shrink-0" @click="close">
                                            <img src="{{ asset(setting('logo_light', 'logo/logo_light.svg')) }}" alt="{{ setting_translated('site_title') ?: config('app.name') }}" class="h-8">
                                        </a>
                                    </template>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <button @click="close" class="p-2" aria-label="Close">
                                            <x-heroicon-o-x-mark class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>

                                <div class="flex-1 overflow-hidden relative">
                                    <template x-for="(level, index) in stack" :key="index">
                                        <div
                                            x-show="index === stack.length - 1"
                                            x-transition:enter="transition transform duration-300 ease-out"
                                            x-transition:enter-start="translate-x-full"
                                            x-transition:enter-end="translate-x-0"
                                            x-transition:leave="transition transform duration-200 ease-in"
                                            x-transition:leave-start="translate-x-0"
                                            x-transition:leave-end="-translate-x-full"
                                            class="absolute inset-0 overflow-y-auto overflow-x-hidden"
                                        >
                                            <div class="py-2 space-y-0.5 divide-y divide-gray-200">
                                                <template x-for="item in level.items" :key="item.id">
                                                    <div>
                                                        <template x-if="!item.children || item.children.length === 0">
                                                            <a
                                                                :href="item.link"
                                                                :target="item.target"
                                                                @click="close"
                                                                class="flex items-center gap-2 px-5 py-3 text-xl text-gray-950 transition-colors mx-2 rounded-lg"
                                                            >
                                                                <span x-text="item.name" class="flex-1"></span>
                                                                <template x-if="item.target === '_blank'">
                                                                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                                                </template>
                                                            </a>
                                                        </template>
                                                        <template x-if="item.children && item.children.length > 0">
                                                            <button
                                                                @click="drillDown(item.children, item.name)"
                                                                class="flex items-center gap-2 w-full px-5 py-3 text-xl text-gray-950 transition-colors text-left mx-2 rounded-lg"
                                                            >
                                                                <span x-text="item.name" class="flex-1"></span>
                                                                <x-heroicon-o-chevron-right class="w-5 h-5 text-primary shrink-0" />
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="border-t border-gray-200 px-5 py-4 space-y-4 shrink-0 bg-white">
                                    <div class="space-y-1">
                                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider px-1">{{ __('Language') }}</p>
                                        @foreach (config('localizer.supported_locales') as $loopLocale)
                                            <a
                                                href="{{ Route::localizedSwitcherUrl($loopLocale) }}"
                                                @class([
                                                    'block px-3 py-2 text-sm rounded-lg transition-colors',
                                                    'font-semibold text-primary bg-primary-50' => app()->getLocale() === $loopLocale,
                                                    'text-gray-600 hover:text-gray-700 hover:bg-gray-50' => app()->getLocale() !== $loopLocale,
                                                ])
                                                @click="close"
                                            >
                                                {{ strtoupper($loopLocale) }} — {{ $languages[$loopLocale] }}
                                            </a>
                                        @endforeach
                                    </div>

                                    @auth
                                        <div class="space-y-1 pt-2">
                                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider px-1">{{ auth()->user()->name }}</p>
                                            <a href="{{ route('customer.dashboard') }}" @click="close" class="block px-3 py-2 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">{{ __('Dashboard') }}</a>
                                            <a href="{{ route('customer.downloads') }}" @click="close" class="block px-3 py-2 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">{{ __('My Downloads') }}</a>
                                            <a href="{{ route('customer.profile') }}" @click="close" class="block px-3 py-2 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">{{ __('Profile') }}</a>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="w-full text-left block px-3 py-2 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">{{ __('Logout') }}</button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="space-y-2 pt-2">
                                            <a href="{{ route('login') }}" @click="close" class="block w-full text-center px-4 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors">{{ __('Login') }}</a>
                                            @if (Route::has('register'))
                                                <a href="{{ route('register') }}" @click="close" class="block w-full text-center px-4 py-2.5 text-sm font-medium text-primary border border-primary rounded-lg hover:bg-primary-50 transition-colors">{{ __('Register') }}</a>
                                            @endif
                                        </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
