<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Resources\EmailTemplates\Pages;

use App\Filament\Clusters\Mail\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
