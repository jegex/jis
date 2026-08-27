<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslatableRouteKey;
use App\Services\SEOTemplateResolver;
use Biostate\FilamentMenuBuilder\Traits\Menuable;
use Database\Factories\PageFactory;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\AlternateTag;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\Schema;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

final class Page extends Model implements HasMedia, HasRichContent
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasSEO, HasTranslatableRouteKey, HasTranslatableSlug, HasTranslations;

    use InteractsWithMedia;
    use InteractsWithRichContent;
    use Menuable;

    public array $translatable = ['title', 'content', 'slug'];

    protected $fillable = [
        'title',
        'content',
        'slug',
        'is_published',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public static function getFilamentSearchLabel(): string
    {
        return 'title';
    }

    public function getMenuLinkAttribute(): string
    {
        return route('pages.show', $this);
    }

    public function getMenuNameAttribute(): string
    {
        return $this->title;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getDynamicSEOData(): SEOData
    {
        $resolver = app(SEOTemplateResolver::class);

        $variables = [
            'title' => $this->title,
            'description' => Str::limit(strip_tags((string) $this->content), 160),
            'site_title' => setting_translated('site_title') ?: config('app.name'),
            'site_description' => setting_translated('site_description') ?? '',
        ];

        $schema = new SchemaCollection([
            fn () => Schema::webPage()
                ->name($variables['title'])
                ->description($variables['description'])
                ->url(route('pages.show', $this))
                ->toArray(),
        ]);

        $seoConfig = setting('seo', []);

        return new SEOData(
            title: $this->seo?->title
                ?? $resolver->resolve(locale_text($seoConfig['page_title'] ?? null), $variables)
                ?? $this->title,
            description: $this->seo?->description
                ?? $resolver->resolve(locale_text($seoConfig['page_description'] ?? null), $variables)
                ?? Str::limit(strip_tags((string) $this->content), 160),
            alternates: collect(config('localizer.supported_locales'))
                ->map(fn (string $locale) => new AlternateTag(
                    hreflang: $locale,
                    href: route('pages.show', ['locale' => $locale, 'page' => $this->getTranslation('slug', $locale)]),
                ))
                ->toArray(),
            schema: $schema,
        );
    }

    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentProvider(
                SpatieMediaLibraryFileAttachmentProvider::make()
                    ->collection('content-file-attachments')
            );
    }

    protected static function booted(): void
    {
        self::addGlobalScope('published', fn (Builder $query) => $query->where('is_published', true));
    }
}
