<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @head
</head>
<body class="font-sans antialiased">
    {{ $slot }}

    @livewire('notifications')
    @filamentScripts
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
