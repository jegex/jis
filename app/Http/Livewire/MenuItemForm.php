<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Biostate\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Biostate\FilamentMenuBuilder\Http\Livewire\MenuItemForm as BaseMenuItemForm;

final class MenuItemForm extends BaseMenuItemForm
{
    public function submit(): void
    {
        $menuItem = array_merge($this->data, [
            'menu_id' => $this->menuId,
        ]);

        $modelClass = FilamentMenuBuilderPlugin::get()->getMenuItemModel();
        $menuItem = $modelClass::query()->create($menuItem);

        $this->form->fill();

        $this->dispatch('menu-item-created', menuId: $this->menuId, menuItemId: $menuItem->id);
    }
}
