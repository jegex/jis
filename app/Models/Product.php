<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CategoryType;
use App\Models\Concerns\HasTranslatableRouteKey;
use App\Services\SEOTemplateResolver;
use Awcodes\RicherEditor\Plugins\CodeBlockShikiPlugin;
use Biostate\FilamentMenuBuilder\Traits\Menuable;
use Database\Factories\ProductFactory;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Phiki\Theme\Theme;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\AlternateTag;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\SchemaOrg\Schema;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

final class Product extends Model implements HasMedia, HasRichContent
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasTranslatableRouteKey, HasTranslatableSlug, HasTranslations, InteractsWithMedia;

    use HasSEO;
    use InteractsWithRichContent;
    use Menuable;

    public array $translatable = ['title', 'description', 'short_description', 'slug'];

    protected $fillable = [
        'title',
        'description',
        'short_description',
        'slug',
        'price',
        'is_published',
        'release_date',
        'category_id',
        'currency_id',
    ];

    protected $hidden = [
        'currency_id',
        'category_id',
        'is_published',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'description' => 'array',
            'price' => MoneyCast::class,
            'is_published' => 'boolean',
            'release_date' => 'datetime',
        ];
    }

    public static function getFilamentSearchLabel(): string
    {
        return 'title';
    }

    public function getMenuLinkAttribute(): string
    {
        return route('products.show', $this);
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
        return $this->belongsTo(Category::class)->where('type', CategoryType::Product->value);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function getCurrencyCodeAttribute(): ?string
    {
        return $this->currency?->code;
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('small')
            ->fit(Fit::Contain, 100, 100)
            ->nonQueued();

        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile();

        $this->addMediaCollection('file')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }

    public function getDynamicSEOData(): SEOData
    {
        $resolver = app(SEOTemplateResolver::class);

        $variables = [
            'title' => $this->title,
            'short_description' => $this->short_description ?? '',
            'description' => Str::limit(strip_tags((string) $this->description), 160),
            'category' => $this->category?->name ?? '',
            'site_title' => setting_translated('site_title') ?: config('app.name'),
            'site_description' => setting_translated('site_description') ?? '',
        ];

        $schema = Schema::product()
            ->name($variables['title'])
            ->description($variables['short_description'] ?: $variables['description']);

        if ($this->seo?->image) {
            $schema->image(secure_url($this->seo->image));
        }

        if ($this->price && $this->currency) {
            $availability = match (true) {
                ! $this->is_published => 'https://schema.org/OutOfStock',
                $this->isPreorder() => 'https://schema.org/PreOrder',
                default => 'https://schema.org/InStock',
            };

            $schema->offers(Schema::offer()
                ->price($this->price)
                ->priceCurrency($this->currency->code)
                ->availability($availability));
        }

        $seoConfig = setting('seo', []);

        return new SEOData(
            title: $this->seo?->title
                ?? $resolver->resolve(locale_text($seoConfig['product_title'] ?? null), $variables)
                ?? $this->title,
            description: $this->seo?->description
                ?? $resolver->resolve(locale_text($seoConfig['product_description'] ?? null), $variables)
                ?? $this->short_description
                ?? Str::limit(strip_tags((string) $this->description), 160),
            image: $this->seo?->image,
            alternates: collect(config('localizer.supported_locales'))
                ->map(fn (string $locale) => new AlternateTag(
                    hreflang: $locale,
                    href: route('products.show', ['locale' => $locale, 'product' => $this->getTranslation('slug', $locale)]),
                ))
                ->toArray(),
            schema: new SchemaCollection([
                fn () => $schema->toArray(),
            ]),
        );
    }

    public function content(): Attribute
    {
        return Attribute::get(function () {
            return RichContentRenderer::make($this->description)
                ->plugins([
                    CodeBlockShikiPlugin::make()
                        ->themes(light: Theme::GithubLight, dark: Theme::GithubDark),
                ])
                ->toHtml();
        });
    }

    public function getExcerptAttribute(): ?string
    {
        return $this->short_description ?? Str::limit(strip_tags((string) $this->description), 160);
    }

    public function isPreorder(): bool
    {
        return $this->release_date !== null && $this->release_date->isFuture();
    }

    public function isReleased(): bool
    {
        return $this->release_date === null || $this->release_date->isPast();
    }

    public function scopePreorder(Builder $query): Builder
    {
        return $query->where('release_date', '>', now());
    }

    public function getPublishedAtAttribute(): mixed
    {
        return $this->created_at;
    }

    protected static function booted(): void
    {
        self::addGlobalScope('published', fn (Builder $query) => $query->where('is_published', true));

        self::saving(function (Model $model) {
            if (! $model->currency_id) {
                $model->currency_id = Currency::getDefault()->id;
            }
        });
    }
}
