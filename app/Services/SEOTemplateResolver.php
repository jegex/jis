<?php

declare(strict_types=1);

namespace App\Services;

final class SEOTemplateResolver
{
    public function resolve(null|array|string $template, array $variables): ?string
    {
        if ($template === null) {
            return null;
        }

        if (is_array($template)) {
            $template = $template[app()->getLocale()] ?? $template[app()->getFallbackLocale()] ?? $template[0];
        }

        if (blank($template)) {
            return null;
        }

        return preg_replace_callback(
            '/%(\w+)%/',
            fn (array $matches) => $variables[$matches[1]] ?? '',
            $template
        );
    }
}
