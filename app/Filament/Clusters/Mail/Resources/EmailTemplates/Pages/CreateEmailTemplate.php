<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Resources\EmailTemplates\Pages;

use App\Filament\Clusters\Mail\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;
}
