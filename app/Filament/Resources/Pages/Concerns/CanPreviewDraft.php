<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Concerns;

use App\Enums\ContentStatus;
use App\Services\PreviewService;
use Illuminate\Database\Eloquent\Model;

trait CanPreviewDraft
{
    public function createDraftRecordForPreview(): Model
    {
        $data = $this->form->getState();

        $data['status'] = ContentStatus::Draft;

        $data = $this->mutateFormDataBeforeCreate($data);

        $this->record = $this->handleRecordCreation($data);

        $this->form->model($this->getRecord())->saveRelationships();

        return $this->record;
    }

    public function previewDraftUrl(Model $record): string
    {
        return app(PreviewService::class)->url(
            $record,
            session()->get('spatie_translatable_active_locale'),
        );
    }

    public function previewDraftEditUrl(Model $record): string
    {
        return static::getResource()::getUrl('edit', ['record' => $record]);
    }
}
