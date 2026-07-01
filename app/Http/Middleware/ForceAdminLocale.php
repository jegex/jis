<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ForceAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = auth()->user()?->admin_locale ?? config('app.fallback_locale', 'en');

        app()->setLocale($locale);

        return $next($request);
    }
}
