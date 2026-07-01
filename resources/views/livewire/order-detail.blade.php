<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('customer.dashboard') }}" class="text-sm text-primary hover:underline">
            &larr; {{ __('Back to Dashboard') }}
        </a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Order') }} #{{ $order->order_number }}</h1>
        <span class="px-3 py-1 text-sm rounded-full {{ $order->status->value === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
            {{ $order->status->getLabel() }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Items') }}</h2>
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between py-3 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                        <div class="flex items-center gap-4">
                            @if($item->product?->getFirstMediaUrl('cover'))
                                <img src="{{ $item->product->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-lg">
                            @else
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs">{{ __('No Cover') }}</div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                                <p class="text-sm text-gray-500">{{ Str::price($item->price, $order->currency_code) }}</p>
                            </div>
                        </div>
                        @if($order->status->value === 'paid' && $item->product)
                            <a href="{{ route('payment.download', ['order' => $order->order_number, 'product' => $item->product]) }}"
                               class="text-sm bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">
                                {{ __('Download') }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Payment History') }}</h2>
                @forelse($order->payments as $payment)
                    <div class="flex items-center justify-between py-3 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $payment->gateway }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ Str::price($payment->amount ?? $order->total, $payment->currency_code ?? $order->currency_code) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $payment->status->value === 'success' ? 'bg-green-100 text-green-700' : ($payment->status->value === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($payment->status->value) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No payment records.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Order Summary') }}</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Order Date') }}</dt>
                        <dd class="text-gray-900">{{ $order->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Subtotal') }}</dt>
                        <dd class="text-gray-900">{{ Str::price($order->subtotal, $order->currency_code) }}</dd>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">{{ __('Discount') }}</dt>
                            <dd class="text-green-600">-{{ Str::price($order->discount, $order->currency_code) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-3">
                        <dt class="text-gray-900">{{ __('Total') }}</dt>
                        <dd class="text-gray-900">{{ Str::price($order->total, $order->currency_code) }}</dd>
                    </div>
                </dl>
            </div>

            @if($order->paid_at)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Status Timeline') }}</h2>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <div class="w-0.5 h-8 bg-green-200"></div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ __('Order Placed') }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ __('Payment Completed') }}</p>
                                <p class="text-xs text-gray-500">{{ $order->paid_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
