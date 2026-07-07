<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

final class OrderDetail extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        if ($order->user_id != auth()->id()) {
            abort(404);
        }

        $this->order = $order->load('items.product.media', 'payments', 'currency');
    }

    public function render()
    {
        return view('livewire.order-detail')
            ->layout('layouts.app');
    }
}
