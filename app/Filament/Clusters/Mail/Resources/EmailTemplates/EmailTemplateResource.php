<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Resources\EmailTemplates;

use App\Filament\Clusters\Mail\MailCluster;
use App\Filament\Clusters\Mail\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use App\Filament\Clusters\Mail\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Clusters\Mail\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Filament\Clusters\Mail\Resources\EmailTemplates\Schemas\EmailTemplateForm;
use App\Filament\Clusters\Mail\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use App\Models\EmailTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $cluster = MailCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return EmailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplates::route('/'),
            'create' => CreateEmailTemplate::route('/create'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
