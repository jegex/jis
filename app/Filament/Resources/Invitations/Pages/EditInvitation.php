<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invitations\Pages;

use App\Filament\Resources\Invitations\InvitationResource;
use Filament\Resources\Pages\EditRecord;

final class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;
}
