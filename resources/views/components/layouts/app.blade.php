<!DOCTYPE html>
@props(['seoData' => null, 'model' => null])
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @head
</head>
<body class="font-sans antialiased">
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
