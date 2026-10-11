<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Your order') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $order->order_number }}</p>

    @if($order->paid_at)
        <p class="text-sm text-green-600 mb-6">{{ __('Payment received') }} — {{ $order->paid_at->format('d M Y') }}</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($order->items as $item)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900">{{ $item->product_name }}</h3>

                @if($item->product)
                    <a href="{{ $this->downloadUrl($item->product->id) }}" class="mt-4 inline-block bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark text-sm">
                        {{ __('Download') }}
                    </a>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">{{ __('No products available.') }}</div>
        @endforelse
    </div>
</div>
