<?php

declare(strict_types=1);

namespace App\Filament\Resources\Currencies\Pages;

use App\Filament\Resources\Currencies\CurrencyResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;
}
