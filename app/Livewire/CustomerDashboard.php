<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

final class CustomerDashboard extends Component
{
    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->paid()
            ->with('items.product', 'items.product.media')
            ->latest()
            ->paginate(10);

        return view('livewire.customer-dashboard', compact('orders'))
            ->layout('layouts.app');
    }
}
