<nav class="sticky top-0 z-50 bg-primary">
    <div class="container xl:px-0!">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset(setting('logo_dark', 'logo/logo_dark.svg')) }}" alt="{{ setting_translated('site_title') ?: config('app.name') }}" class="h-8">
                </a>
                <div class="hidden md:flex items-center space-x-1">
                    <x-filament-menu-builder::menu slug="main-menu" view="components.menu.header" />
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <x-mobile-slideover />
                @php
                    $languages = config('localizer.locale_with_label');
                @endphp
                <div class="hidden md:block group relative">
                    <button class="flex items-center gap-1 text-white/80 hover:text-white px-2 py-2 rounded-md text-sm font-medium whitespace-nowrap">
                        {{ strtoupper(app()->getLocale()) }}
                        <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 group-hover:rotate-180" />
                    </button>
                    <ul class="menu-dropdown absolute right-0 top-full bg-white rounded-md shadow-lg py-2 min-w-max z-50 space-y-1">
                        @foreach (config('localizer.supported_locales') as $loopLocale)
                            <li>
                                <a href="{{ Route::localizedSwitcherUrl($loopLocale) }}"
                                   @class([
                                       'block px-4 py-1.5 text-sm whitespace-nowrap',
                                       'font-bold text-primary' => app()->getLocale() === $loopLocale,
                                       'text-gray-600 hover:text-gray-700 hover:bg-gray-100' => app()->getLocale() !== $loopLocale,
                                   ])>
                                    {{ strtoupper($loopLocale) }} ({{ $languages[$loopLocale] }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @auth
                    <div class="hidden md:block group relative">
                        <button class="flex items-center gap-1 text-white/80 hover:text-white px-2 py-2 rounded-md text-sm font-medium whitespace-nowrap">
                            {{ auth()->user()->name }}
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 group-hover:rotate-180" />
                        </button>
                        <ul class="menu-dropdown absolute right-0 top-full bg-white rounded-md shadow-lg py-2 min-w-max z-50 space-y-1">
                            <li>
                                <a href="{{ route('customer.dashboard') }}" class="block px-4 py-1.5 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-100 whitespace-nowrap">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.downloads') }}" class="block px-4 py-1.5 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-100 whitespace-nowrap">
                                    {{ __('My Downloads') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.profile') }}" class="block px-4 py-1.5 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-100 whitespace-nowrap">
                                    {{ __('Profile') }}
                                </a>
                            </li>
                            <li class="border-t border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-1.5 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-100 whitespace-nowrap">
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden md:inline-flex nav-link">
                        {{ __('Login') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
