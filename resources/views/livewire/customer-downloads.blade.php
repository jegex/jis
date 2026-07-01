<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('My Downloads') }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($orders as $order)
            @foreach($order->items as $item)
                @if($item->product)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $item->product_name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Purchased on') }} {{ $order->paid_at?->format('d M Y') }}</p>
                        <a href="{{ route('payment.download', ['order' => $order->order_number, 'product' => $item->product]) }}" class="mt-4 inline-block bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark text-sm">
                            {{ __('Download') }}
                        </a>
                    </div>
                @endif
            @endforeach
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                {{ __('No downloads available.') }}
            </div>
        @endforelse
    </div>
</div>
