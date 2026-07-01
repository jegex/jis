<?php

declare(strict_types=1);

use NielsNumbers\LaravelLocalizer\Detectors\BrowserDetector;
use NielsNumbers\LaravelLocalizer\Detectors\UserDetector;

return [
    'locale_with_label' => [
        'en' => 'English',
        'id' => 'Indonesia',
    ],
    'supported_locales' => ['en', 'id'],

    'hide_default_locale' => true,

    'redirect_enabled' => true,

    'persist_locale' => [
        'session' => true,
        'cookie' => true,
    ],

    'detectors' => [
        UserDetector::class,
        BrowserDetector::class,
    ],

    // Per-locale override for writing direction. Keys must match the
    // locale codes used in `supported_locales`. Values: 'rtl' or 'ltr'.
    // Wins over the script-based detection in `LocaleDirection`.
    'locale_directions' => [],
];
