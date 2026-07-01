<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Services\SEOTemplateResolver;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\AlternateTag;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\SchemaOrg\Schema;

final class HomeController extends Controller
{
    public function __invoke()
    {
        $blocks = setting('homepage_blocks', []);

        $resolvedBlocks = [];
        $needsProducts = false;
        $needsPosts = false;
        $needsProjects = false;

        $productConfig = [];
        $postConfig = [];
        $projectConfig = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];

            if ($type === 'featured-products') {
                $needsProducts = true;
                $productConfig = $data;
            } elseif ($type === 'latest-posts') {
                $needsPosts = true;
                $postConfig = $data;
            } elseif ($type === 'projects') {
                $needsProjects = true;
                $projectConfig = $data;
            }
        }

        $products = $needsProducts
            ? $this->resolveProducts($productConfig)
            : collect();

        $posts = $needsPosts
            ? $this->resolvePosts($postConfig)
            : collect();

        $projects = $needsProjects
            ? $this->resolveProjects($projectConfig)
            : collect();

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];
            $items = match ($type) {
                'featured-products' => $products,
                'latest-posts' => $posts,
                'projects' => $projects,
                default => collect(),
            };

            $resolvedBlocks[] = [
                'type' => $type,
                'data' => $data,
                'items' => $items,
            ];
        }

        $resolver = app(SEOTemplateResolver::class);

        $seoConfig = setting('seo', []);

        $variables = [
            'site_title' => setting_translated('site_title') ?: config('app.name'),
            'site_description' => setting_translated('site_description') ?? '',
        ];

        $logo = setting('logo_light') ?: setting('logo_dark');
        $socialLinks = collect(setting('social', []))->pluck('url')->filter()->toArray();

        $organizationSchema = Schema::organization()
            ->name(setting_translated('site_title') ?: config('app.name'))
            ->url(route('home'))
            ->description($variables['site_description']);

        if ($logo) {
            $organizationSchema->logo(secure_url($logo));
        }

        if ($socialLinks) {
            $organizationSchema->sameAs($socialLinks);
        }

        $homeImage = $seoConfig['homepage_image'] ?? null ?: config('seo.image.fallback');

        if ($homeImage) {
            $organizationSchema->image(secure_url($homeImage));
        }

        $title = $resolver->resolve(locale_text($seoConfig['homepage_title'] ?? null), $variables)
            ?? config('app.name');

        $description = $resolver->resolve(locale_text($seoConfig['homepage_description'] ?? null), $variables)
            ?? '';

        $seoData = new SEOData(
            title: $title,
            description: $description,
            openGraphTitle: $title,
            image: $homeImage,
            alternates: collect(config('localizer.supported_locales'))
                ->filter(fn (string $locale) => $locale !== config('app.locale'))
                ->map(fn (string $locale) => new AlternateTag(
                    hreflang: $locale,
                    href: route('home', ['locale' => $locale]),
                ))
                ->toArray(),
            schema: new SchemaCollection([
                fn () => $organizationSchema->toArray(),
                fn () => Schema::webSite()
                    ->name(setting_translated('site_title') ?: config('app.name'))
                    ->url(route('home'))
                    ->toArray(),
            ]),
        );

        return view('pages.home', compact('resolvedBlocks', 'seoData'));
    }

    private function resolveProducts(array $config): mixed
    {
        $query = Product::with('category', 'media')->where('is_published', true);

        if (! empty($config['count'])) {
            $query->take((int) $config['count']);
        }

        $sortBy = $config['sort_by'] ?? 'latest';
        match ($sortBy) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderBy('title'),
            default => $query->latest(),
        };

        if (! empty($config['category_id'])) {
            $query->where('category_id', $config['category_id']);
        }

        return $query->get();
    }

    private function resolvePosts(array $config): mixed
    {
        $query = Post::with('category', 'author', 'media')->where('is_published', true);

        if (! empty($config['count'])) {
            $query->take((int) $config['count']);
        }

        $sortBy = $config['sort_by'] ?? 'latest';
        match ($sortBy) {
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        if (! empty($config['category_id'])) {
            $query->where('category_id', $config['category_id']);
        }

        return $query->get();
    }

    private function resolveProjects(array $config): mixed
    {
        $query = Project::query();

        if (! empty($config['count'])) {
            $query->take((int) $config['count']);
        }

        $sortBy = $config['sort_by'] ?? 'latest';
        match ($sortBy) {
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderByDesc('date'),
        };

        return $query->get()->groupBy('type');
    }
}
