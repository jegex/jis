@props(['seoData' => null, 'model' => null])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

@if($seoData)
    {!! seo()->for($seoData) !!}
@elseif($model)
    {!! seo()->for($model) !!}
@else
    <title>{{ setting_translated('site_title') ?: config('app.name') }}</title>
    <meta name="description" content="{{ setting_translated('site_description') ?: '' }}">
    <meta property="og:title" content="{{ setting_translated('site_title') ?: config('app.name') }}">
    <meta property="og:description" content="{{ setting_translated('site_description') ?: '' }}">
    <meta name="twitter:title" content="{{ setting_translated('site_title') ?: config('app.name') }}">
    <meta name="twitter:description" content="{{ setting_translated('site_description') ?: '' }}">
    <link rel="shortcut icon" href="{{ secure_asset(setting('favicon')) }}" type="image/x-icon">
@endif

<link rel="preconnect" href="https://rsms.me/">
<link rel="stylesheet" href="https://rsms.me/inter/inter.css">

<style>[x-cloak] { display: none !important; }</style>

@vite(['resources/css/app.css'])

@stack('head')

{!! setting('before_head') !!}
