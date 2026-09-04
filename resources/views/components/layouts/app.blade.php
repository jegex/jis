<!DOCTYPE html>
@props(['seoData' => null, 'model' => null])
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @head
</head>
<body class="font-sans antialiased">
    @if(request()->has('preview'))
        <div class="sticky top-0 z-50 flex items-center justify-between gap-4 bg-amber-400 px-4 py-2 text-sm font-semibold text-amber-950">
            <div class="flex items-center gap-2">
                <x-heroicon-o-eye class="h-5 w-5" />
                <span>{{ __('Preview mode') }}</span>
                <span class="text-amber-800">— {{ __('this content has not been published yet') }}</span>
            </div>
            <a href="{{ url('/admin') }}" class="rounded-full bg-amber-950 px-3 py-1 text-white hover:bg-amber-900">
                {{ __('Back to admin') }}
            </a>
        </div>
    @endif
    <x-header />
    <main>
        {{ $slot }}
    </main>
    <x-footer />
    <x-back-to-top />

    @livewire('notifications')
    @filamentScripts
    @vite('resources/js/app.js')
    @stack('scripts')
    {!! setting('before_body') !!}
</body>
</html>
