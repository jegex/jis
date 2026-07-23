<?php

declare(strict_types=1);

namespace App\Filament\Schemas;

use Filament\Schemas\Schema;
use RalphJSmit\Filament\SEO\SEO;

final class SeoSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SEO::make()
                    ->columnSpanFull(),
            ]);
    }
}
