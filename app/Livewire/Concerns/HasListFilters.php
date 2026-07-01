<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

trait HasListFilters
{
    use WithPagination;

    public string $search = '';

    public string $sort = 'newest';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'sort', 'categoryId', 'tagId']);
        $this->resetPage();
    }
}
