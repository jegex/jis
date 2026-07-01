@php
    $appName = setting_translated('site_title') ?: config('app.name');
    $logoLight = setting('logo_light', 'logo/logo.svg');
    $footerDescription = setting_translated('footer_description');
    $footerCopyright = setting_translated('footer_copyright') ?: ('&copy; :year ' . $appName . '. All rights reserved.');
    $phone = setting('contact_phone', '+62 21 1234 5678');
    $email = setting('contact_email', 'hello@jis-marine.com');
    $address = setting('contact_address');
    $socialItems = setting('social', []);
    $copyright = str_replace(':year', date('Y'), $footerCopyright);
@endphp

<footer class="bg-white border-t border-gray-200">
    <div class="container border-x border-x-gray-200 py-12 md:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 gap-y-12">
            <div>
                <img src="{{ asset($logoLight) }}" alt="{{ $appName }}" class="h-10 mb-4">
                <p class="text-gray-500 text-sm leading-relaxed">
                    {{ $footerDescription }}
                </p>
                @if($socialItems)
                    <div class="flex items-center gap-4 mt-6">
                        @foreach($socialItems as $socialItem)
                            <a href="{{ $socialItem['url'] }}" aria-label="{{ $socialItem['name'] }}" class="text-gray-400 hover:text-primary transition-colors">
                                {!! $socialItem['icon_svg'] ?? '' !!}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-xs font-semibold tracking-widest text-gray-900 uppercase mb-4">{{ __('Quick Links') }}</h3>
                <div class="flex flex-col gap-3">
                    <x-filament-menu-builder::menu slug="footer-menu" view="components.menu.footer" />
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold tracking-widest text-gray-900 uppercase mb-4">{{ __('Contact') }}</h3>
                <ul class="flex flex-col gap-4">
                    <li class="flex items-start gap-3 text-sm text-gray-500">
                        <x-heroicon-o-map-pin class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                        <span>{{ $address }}</span>
                    </li>
                    <li>
                        <a href="tel:{{ $phone }}" class="flex items-start gap-3 text-sm text-gray-500 hover:text-primary transition-colors">
                            <x-heroicon-o-phone class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                            <span>{{ $phone }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ $email }}" class="flex items-start gap-3 text-sm text-gray-500 hover:text-primary transition-colors">
                            <x-heroicon-o-envelope class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                            <span>{{ $email }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-8 mt-12 md:mt-16 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-400 order-2 sm:order-1">{!! $copyright !!}</p>

        </div>
    </div>
</footer>
