<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings;

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function pages(): array
    {
        return [
            Pages\Settings::class,
            Pages\SEO::class,
        ];
    }
}
