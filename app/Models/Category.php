<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use App\Services\SEOTemplateResolver;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\SchemaOrg\Schema;
use Spatie\Translatable\HasTranslations;

final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasTranslations;

    use HasSEO;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'type',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_published' => 'boolean',
        ];
    }

    public function getDynamicSEOData(): SEOData
    {
        $resolver = app(SEOTemplateResolver::class);

        $variables = [
            'name' => $this->name,
            'description' => $this->description ?? '',
            'site_title' => setting_translated('site_title') ?: config('app.name'),
            'site_description' => setting_translated('site_description') ?? '',
        ];

        $seoConfig = setting('seo', []);

        return new SEOData(
            title: $this->seo?->title
                ?? $resolver->resolve(locale_text($seoConfig['category_title'] ?? null), $variables)
                ?? $this->name,
            description: $this->seo?->description
                ?? $resolver->resolve(locale_text($seoConfig['category_description'] ?? null), $variables)
                ?? $this->description,
            schema: new SchemaCollection([
                fn () => Schema::collectionPage()
                    ->name($variables['name'])
                    ->description($variables['description'])
                    ->toArray(),
            ]),
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->withoutGlobalScope('published');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'type_id', 'id');
    }

    protected static function booted(): void
    {
        self::addGlobalScope('published', fn (Builder $query) => $query->where('is_published', true));
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
