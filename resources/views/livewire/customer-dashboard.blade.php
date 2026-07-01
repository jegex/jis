<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('My Orders') }}</h1>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Order') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Product') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <a href="{{ route('customer.order.show', $order->order_number) }}" class="text-primary hover:underline">#{{ $order->order_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $order->items->first()?->product_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ Str::price($order->total, $order->currency_code) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $order->status->value === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $order->status->getLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($order->status->value === 'paid' && $order->items->first()?->product)
                                <a href="{{ route('payment.download', ['order' => $order->order_number, 'product' => $order->items->first()->product]) }}"
                                   class="text-primary hover:underline">
                                    {{ __('Download') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">{{ __('No orders yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
