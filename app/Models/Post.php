<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use App\Models\Concerns\HasTranslatableRouteKey;
use App\Services\SEOTemplateResolver;
use Biostate\FilamentMenuBuilder\Traits\Menuable;
use Database\Factories\PostFactory;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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

final class Post extends Model implements HasMedia, HasRichContent
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasTranslatableRouteKey, HasTranslatableSlug, HasTranslations, InteractsWithMedia;

    use HasSEO;
    use InteractsWithRichContent;
    use Menuable;

    public array $translatable = ['title', 'content', 'excerpt', 'slug'];

    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'slug',
        'is_published',
        'category_id',
        'author_id',
        'published_at',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public static function getFilamentSearchLabel(): string
    {
        return 'title';
    }

    public function getMenuLinkAttribute(): string
    {
        return route('blog.show', $this);
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->where('type', CategoryType::Post->value);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->useDisk('public')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(300)->height(200);
            });
    }

    public function getDynamicSEOData(): SEOData
    {
        $resolver = app(SEOTemplateResolver::class);

        $variables = [
            'title' => $this->title,
            'excerpt' => $this->excerpt ?? '',
            'description' => Str::limit(strip_tags((string) $this->content), 160),
            'author' => $this->author?->name ?? '',
            'category' => $this->category?->name ?? '',
            'published_at' => $this->published_at?->toDateString() ?? '',
            'site_title' => setting_translated('site_title') ?: config('app.name'),
            'site_description' => setting_translated('site_description') ?? '',
        ];

        $schema = Schema::article()
            ->headline($variables['title'])
            ->description($variables['excerpt'] ?: $variables['description'])
            ->datePublished($this->published_at?->toIso8601String())
            ->dateModified($this->updated_at->toIso8601String())
            ->author($variables['author'] ?: config('app.name'));

        if ($this->seo?->image) {
            $schema->image(secure_url($this->seo->image));
        }

        $seoConfig = setting('seo', []);

        return new SEOData(
            title: $this->seo?->title
                ?? $resolver->resolve(locale_text($seoConfig['post_title'] ?? null), $variables)
                ?? $this->title,
            description: $this->seo?->description
                ?? $resolver->resolve(locale_text($seoConfig['post_description'] ?? null), $variables)
                ?? $this->excerpt
                ?? Str::limit(strip_tags((string) $this->content), 160),
            author: $this->seo?->author ?? $this->author?->name,
            image: $this->seo?->image,
            published_time: $this->published_at ?? $this->created_at,
            modified_time: $this->updated_at,
            alternates: collect(config('localizer.supported_locales'))
                ->map(fn (string $locale) => new AlternateTag(
                    hreflang: $locale,
                    href: route('blog.show', ['locale' => $locale, 'post' => $this->getTranslation('slug', $locale)]),
                ))
                ->toArray(),
            schema: new SchemaCollection([
                fn () => $schema->toArray(),
            ]),
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
