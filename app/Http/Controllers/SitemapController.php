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

        foreach ($locales as $locale) {
            $url = Url::create(route('home', ['locale' => $locale]))->setPriority(1.0);
            $this->addAlternates($url, 'home', $locales, $defaultLocale);
            $sitemap->add($url);
        }

        foreach ($locales as $locale) {
            $url = Url::create(route('products.index', ['locale' => $locale]))->setPriority(0.9);
            $this->addAlternates($url, 'products.index', $locales, $defaultLocale);
            $sitemap->add($url);
        }

        foreach ($locales as $locale) {
            $url = Url::create(route('blog.index', ['locale' => $locale]))->setPriority(0.9);
            $this->addAlternates($url, 'blog.index', $locales, $defaultLocale);
            $sitemap->add($url);
        }

        Page::where('is_published', true)->each(function (Page $page) use ($sitemap, $locales, $defaultLocale) {
            foreach ($locales as $locale) {
                $params = $this->routeParams($page, $locale);
                $url = Url::create(route('pages.show', $params))
                    ->setLastModificationDate($page->updated_at)
                    ->setPriority(0.8);

                $this->addAlternates($url, 'pages.show', $locales, $defaultLocale, $page);
                $sitemap->add($url);
            }
        });

        Product::where('is_published', true)->each(function (Product $product) use ($sitemap, $locales, $defaultLocale) {
            foreach ($locales as $locale) {
                $params = $this->routeParams($product, $locale);
                $url = Url::create(route('products.show', $params))
                    ->setLastModificationDate($product->updated_at)
                    ->setPriority(0.8);

                $this->addAlternates($url, 'products.show', $locales, $defaultLocale, $product);
                $sitemap->add($url);
            }
        });

        Post::where('is_published', true)->latest('published_at')->each(function (Post $post) use ($sitemap, $locales, $defaultLocale) {
            foreach ($locales as $locale) {
                $params = $this->routeParams($post, $locale);
                $url = Url::create(route('blog.show', $params))
                    ->setLastModificationDate($post->updated_at)
                    ->setPriority(0.8);

                $this->addAlternates($url, 'blog.show', $locales, $defaultLocale, $post);
                $sitemap->add($url);
            }
        });

        return $sitemap->toResponse(request());
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
