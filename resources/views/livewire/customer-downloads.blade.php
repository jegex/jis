<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('My Downloads') }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($orders as $order)
            @foreach($order->items as $item)
                @if($item->product)
                    @php
                        $isPreorder = $item->product->isPreorder();
                        $canDownload = !$isPreorder || $order->preorder_released_at !== null;
                    @endphp
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $item->product->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Purchased on') }} {{ $order->paid_at?->format('d M Y') }}</p>

                        @if($isPreorder && !$canDownload)
                            <div class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                <x-heroicon-o-clock class="w-3.5 h-3.5"/>
                                {{ __('Pending Release') }} &mdash; {{ $item->product->release_date?->translatedFormat('j F Y') }}
                            </div>
                        @endif

                        @if($order->invoice)
                            <a href="{{ route('invoices.download', $order->invoice) }}" target="_blank" class="mt-4 inline-block bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-black text-sm mr-2">
                                {{ __('Invoice PDF') }}
                            </a>
                        @endif

                        @if($canDownload)
                            <a href="{{ route('payment.download', ['order' => $order->order_number, 'product' => $item->product]) }}" class="mt-4 inline-block bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark text-sm">
                                {{ __('Download') }}
                            </a>
                        @endif
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
