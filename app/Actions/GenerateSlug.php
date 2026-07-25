<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Str;

final class GenerateSlug extends AbstractAction
{
    public function handle(
        string $title,
        string $modelClass,
        string $locale,
        ?int $ignoreId = null,
        string $column = 'slug',
        ?callable $modifyRule = null,
    ): string {
        $slug = Str::slug($title);

        if ($slug === '') {
            return $slug;
        }

        $baseSlug = $slug;
        $counter = 1;

        while ($this->slugExists($modelClass, $slug, $locale, $ignoreId, $column, $modifyRule)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(
        string $modelClass,
        string $slug,
        string $locale,
        ?int $ignoreId,
        string $column,
        ?callable $modifyRule,
    ): bool {
        $query = $modelClass::withoutGlobalScopes();

        $isJsonColumn = in_array($column, $modelClass::$translatable ?? [], true);

        if ($isJsonColumn) {
            $query->whereJsonContainsLocale($column, $locale, $slug);
        } else {
            $query->where($column, $slug);
        }

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($modifyRule !== null) {
            $query = $modifyRule($query);
        }

        return $query->exists();
    }
}
