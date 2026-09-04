<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class PreviewService
{
    public const TYPES = [
        'page' => Page::class,
        'post' => Post::class,
        'product' => Product::class,
    ];

    private const PROVISIONAL_TTL_SECONDS = 600;

    public function typeFor(Model $model): string
    {
        $type = array_search($model::class, self::TYPES, true);

        if ($type === false) {
            $class = get_class($model);
            throw new InvalidArgumentException("Unsupported preview model [{$class}].");
        }

        return $type;
    }

    public function resolveModel(string $type): string
    {
        $model = self::TYPES[$type] ?? null;

        if ($model === null) {
            abort(404);
        }

        return $model;
    }

    public function url(Model $model, ?string $locale = null): string
    {
        $type = $this->typeFor($model);

        return URL::signedRoute('preview.show', [
            'type' => $type,
            'record' => $model->getKey(),
            'locale' => $locale,
            'preview' => 1,
        ]);
    }

    /**
     * Build a preview URL that renders unsaved form data without persisting it.
     *
     * @param  array<string, mixed>  $data
     */
    public function provisionalUrl(Model $model, array $data, ?string $locale = null): string
    {
        $key = Str::uuid()->toString();

        Cache::put("preview:provisional:{$key}", [
            'type' => $this->typeFor($model),
            'record' => $model->getKey(),
            'locale' => $locale,
            'data' => $data,
        ], self::PROVISIONAL_TTL_SECONDS);

        return URL::signedRoute('preview.provisional', [
            'key' => $key,
            'preview' => 1,
        ]);
    }

    /**
     * @return array{type: string, record: int|string, locale: ?string, data: array<string, mixed>}|null
     */
    public function resolveProvisional(string $key): ?array
    {
        $payload = Cache::get("preview:provisional:{$key}");

        if (! is_array($payload)) {
            return null;
        }

        return $payload;
    }
}
