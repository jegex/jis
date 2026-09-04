<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Pages\RevisionsPage;
use App\Filament\Resources\Posts\PostResource;

final class PostRevisions extends RevisionsPage
{
    protected static string $resource = PostResource::class;

    public function getTitle(): string
    {
        return __('Revisions');
    }
}
