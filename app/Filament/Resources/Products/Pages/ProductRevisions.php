<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Pages\RevisionsPage;
use App\Filament\Resources\Products\ProductResource;

final class ProductRevisions extends RevisionsPage
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return __('Revisions');
    }
}
