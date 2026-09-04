<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages;

use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Slider\Enums\PipsMode;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

abstract class RevisionsPage extends Page
{
    use HasFiltersAction;
    use InteractsWithRecord;

    public int|string|null $leftId = null;

    public int|string|null $rightId = null;

    public ?array $compare = null;

    protected string $view = 'filament.resources.revisions';

    final public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if ($record === null) {
            return false;
        }

        return (bool) (auth()->user()?->can('view', $record));
    }

    final public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $ids = $this->revisions()->pluck('id');

        $this->leftId = $ids->last();
        $this->rightId = $ids->first();
        $this->compare = [$this->leftId, $this->rightId];
    }

    final public function getRevisions(): Collection
    {
        return $this->getRecord()
            ->activitiesAsSubject()
            ->latest()
            ->orderByDesc('id')
            ->with('causer')
            ->get();
    }

    final public function getLeftRevision(): ?Activity
    {
        return $this->revision((int) $this->leftId);
    }

    final public function getRightRevision(): ?Activity
    {
        return $this->revision((int) $this->rightId);
    }

    final public function getChanges(): array
    {
        $left = $this->getLeftRevision();
        $right = $this->getRightRevision();

        if ($left === null || $right === null) {
            return [];
        }

        return $this->diffAttributes(
            $left->attribute_changes?->get('attributes') ?? [],
            $right->attribute_changes?->get('attributes') ?? [],
        );
    }

    final public function compare(int $id): void
    {
        if ($this->rightId !== null && (int) $this->rightId !== $id) {
            $this->leftId = $this->rightId;
        }

        $this->rightId = $id;
    }

    final public function prev(): void
    {
        $currentIndex = $this->getCurrentRevisionIndex();
        $revisions = $this->revisions();

        if ($currentIndex !== false && $currentIndex < $revisions->count() - 1) {
            $this->leftId = $this->rightId;
            $this->rightId = $revisions[$currentIndex + 1]->id;
        }
    }

    final public function next(): void
    {
        $currentIndex = $this->getCurrentRevisionIndex();
        $revisions = $this->revisions();

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->leftId = $this->rightId;
            $this->rightId = $revisions[$currentIndex - 1]->id;
        }
    }

    final public function hasPrev(): bool
    {
        $currentIndex = $this->getCurrentRevisionIndex();

        return $currentIndex !== false && $currentIndex < $this->revisions()->count() - 1;
    }

    final public function hasNext(): bool
    {
        $currentIndex = $this->getCurrentRevisionIndex();

        return $currentIndex !== false && $currentIndex > 0;
    }

    final public function canRestore(): bool
    {
        if (! auth()->user()?->can('update', $this->getRecord())) {
            return false;
        }

        if (! $this->hasNext()) {
            return false;
        }

        $right = $this->getRightRevision();

        return $right !== null
            && ($right->attribute_changes?->get('attributes') ?? []) !== [];
    }

    final public function restoreRevision(): void
    {
        abort_unless($this->canRestore(), 403);

        $right = $this->getRightRevision();
        $attributes = $right?->attribute_changes?->get('attributes') ?? [];

        if ($attributes === []) {
            return;
        }

        $record = $this->getRecord();
        $raw = $record->getAttributes();

        foreach ($attributes as $key => $value) {
            $raw[$key] = $value;
        }

        $record->setRawAttributes($raw);
        $record->save();

        $ids = $this->revisions()->pluck('id');

        $this->leftId = $ids->last();
        $this->rightId = $ids->first();
        $this->compare = [$this->leftId, $this->rightId];

        Notification::make()
            ->success()
            ->title(__('Revision restored'))
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Compare')
                ->slideOver(false)
                ->schema([
                    Slider::make('compare')
                        ->range(
                            minValue: 0,
                            maxValue: $this->revisions()->count()
                        )
                        ->pips(PipsMode::Values)
                        ->pipsValues(fn () => $this->revisions()->pluck('id')->toArray())
                        ->step(1)
                        ->default($this->compare),
                ]),
        ];
    }

    private function getCurrentRevisionIndex(): int|false
    {
        return $this->revisions()->search(fn ($revision) => $revision->id === (int) $this->rightId);
    }

    private function revisions(): Collection
    {
        return $this->getRevisions();
    }

    private function revision(int $id): ?Activity
    {
        return $this->revisions()->firstWhere('id', $id);
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @return array<string, array{new: mixed, old: mixed}>
     */
    private function diffAttributes(array $old, array $new): array
    {
        $changes = [];

        /** @var list<string> $fields */
        $fields = array_values(array_unique([...array_keys($old), ...array_keys($new)]));

        foreach ($fields as $field) {
            $key = (string) $field;
            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;

            if ($before === $after) {
                continue;
            }

            $changes[$key] = [
                'new' => $after,
                'old' => $before,
            ];
        }

        return $changes;
    }
}
