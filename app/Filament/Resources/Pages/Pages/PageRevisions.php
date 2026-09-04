<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\RevisionsPage;

final class PageRevisions extends RevisionsPage
{
    protected static string $resource = PageResource::class;

    public function getTitle(): string
    {
        return __('Revisions');
    }
}
