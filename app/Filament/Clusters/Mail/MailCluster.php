<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class MailCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function pages(): array
    {
        return [
            Pages\SendNewsletter::class,
        ];
    }
}
