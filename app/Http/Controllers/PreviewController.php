<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PreviewService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class PreviewController extends Controller
{
    public function __construct(
        private PreviewService $previewService,
    ) {}

    public function __invoke(Request $request, string $type, int $record)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired preview link.');
        }

        $modelClass = $this->previewService->resolveModel($type);

        $model = $modelClass::withoutGlobalScope('published')->findOrFail($record);

        return $this->render($type, $model);
    }

    public function provisional(Request $request, string $key)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired preview link.');
        }

        $payload = $this->previewService->resolveProvisional($key);

        if ($payload === null) {
            abort(404, 'Preview data has expired.');
        }

        $modelClass = $this->previewService->resolveModel($payload['type']);

        /** @var Model $model */
        $model = $modelClass::withoutGlobalScope('published')->findOrFail($payload['record']);

        $this->applyProvisionalData($model, $payload['data'], $payload['locale']);

        return $this->render($payload['type'], $model);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyProvisionalData(Model $model, array $data, ?string $locale): void
    {
        if ($locale !== null && method_exists($model, 'setLocale')) {
            $model->setLocale($locale);
        }

        $translatable = method_exists($model, 'getTranslatableAttributes')
            ? $model->getTranslatableAttributes()
            : [];

        $nonTranslatableKeys = array_values(array_diff(
            array_keys($data),
            $translatable,
        ));

        $fillable = $model->getFillable();

        foreach ($nonTranslatableKeys as $key) {
            if (! in_array($key, $fillable, true)) {
                continue;
            }

            $model->setAttribute($key, $data[$key]);
        }

        if (method_exists($model, 'setTranslation')) {
            $effectiveLocale = $locale ?? $model->getLocale();

            foreach (array_intersect_key($data, array_flip($translatable)) as $key => $value) {
                if (is_array($value)) {
                    $value = $value[$effectiveLocale] ?? $value[array_key_first($value)] ?? '';
                }

                $model->setTranslation($key, $effectiveLocale, $value);
            }
        }
    }

    private function render(string $type, $model)
    {
        return match ($type) {
            'page' => $this->renderPage($model),
            'post' => $this->renderPost($model),
            'product' => $this->renderProduct($model),
        };
    }

    private function renderPage($page)
    {
        $page->loadMissing('media');

        $this->preparePreviewSeo($page);

        return view('pages.page-show', compact('page'))
            ->with('model', $page)
            ->with('preview', true);
    }

    private function renderPost($post)
    {
        $post->loadMissing('category', 'author', 'media', 'tags');

        $this->preparePreviewSeo($post);

        $relatedPosts = $post::withoutGlobalScope('published')
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->with('category', 'author', 'media')
            ->latest('published_at')
            ->take(3)
            ->get();

        $recentPosts = $post::withoutGlobalScope('published')
            ->where('id', '!=', $post->id)
            ->with('category', 'author', 'media')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts', 'recentPosts'))
            ->with('model', $post)
            ->with('preview', true);
    }

    private function renderProduct($product)
    {
        $product->loadMissing('category', 'currency', 'media', 'tags');

        $this->preparePreviewSeo($product);

        return view('preview.product', compact('product'))
            ->with('model', $product)
            ->with('preview', true);
    }

    private function preparePreviewSeo($model): void
    {
        if (! $model->seo) {
            return;
        }

        if (method_exists($model, 'getDynamicSEOData') === false) {
            return;
        }

        $model->seo->setRelation(
            'model',
            $model::withoutGlobalScope('published')->find($model->getKey()),
        );
    }
}
