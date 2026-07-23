<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use NielsNumbers\LaravelLocalizer\Facades\Localizer;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

final class SitemapController extends Controller
{
    public function index()
    {
        $locales = config('localizer.supported_locales', ['en']);
        $defaultLocale = Localizer::defaultLocale();

        $sitemap = Sitemap::create();

        $this->addStaticUrls($sitemap, $locales, $defaultLocale);
        $this->addModelUrls($sitemap, $locales, $defaultLocale);

        return $sitemap->toResponse(request());
    }

    private function addStaticUrls(Sitemap $sitemap, array $locales, string $defaultLocale): void
    {
        foreach (['home', 'products.index', 'blog.index'] as $routeName) {
            $priority = $routeName === 'home' ? 1.0 : 0.9;

            foreach ($locales as $locale) {
                $url = Url::create(route($routeName, ['locale' => $locale]))->setPriority($priority);
                $this->addAlternates($url, $routeName, $locales, $defaultLocale);
                $sitemap->add($url);
            }
        }
    }

    private function addModelUrls(Sitemap $sitemap, array $locales, string $defaultLocale): void
    {
        $modelRoutes = [
            Page::class => 'pages.show',
            Product::class => 'products.show',
            Post::class => 'blog.show',
        ];

        foreach ($modelRoutes as $modelClass => $routeName) {
            $query = $modelClass::where('is_published', true);

            if ($modelClass === Post::class) {
                $query->latest('published_at');
            }

            $query->each(function ($model) use ($sitemap, $locales, $defaultLocale, $routeName) {
                foreach ($locales as $locale) {
                    $params = $this->routeParams($model, $locale);
                    $url = Url::create(route($routeName, $params))
                        ->setLastModificationDate($model->updated_at)
                        ->setPriority(0.8);

                    $this->addAlternates($url, $routeName, $locales, $defaultLocale, $model);
                    $sitemap->add($url);
                }
            });
        }
    }

    private function addAlternates(Url $url, string $name, array $locales, string $defaultLocale, mixed $model = null): void
    {
        foreach ($locales as $locale) {
            $params = $model
                ? $this->routeParams($model, $locale)
                : ['locale' => $locale];

            $url->addAlternate(route($name, $params), $locale);
        }

        $defaultParams = $model
            ? $this->routeParams($model, $defaultLocale)
            : ['locale' => $defaultLocale];

        $url->addAlternate(route($name, $defaultParams), 'x-default');
    }

    private function routeParams(mixed $model, string $locale): array
    {
        $params = ['locale' => $locale];

        if ($model instanceof Page) {
            $params['page'] = $model->getTranslation('slug', $locale);
        } elseif ($model instanceof Product) {
            $params['product'] = $model;
        } elseif ($model instanceof Post) {
            $params['post'] = $model;
        }

        return $params;
    }
}
