<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Checkout') }}</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center gap-4">
            @if($product->getFirstMediaUrl('cover', 'thumb'))
                <img src="{{ $product->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $product->title }}" class="w-20 h-20 object-cover rounded">
            @endif
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $product->title }}</h2>
                <p class="text-2xl font-bold text-primary mt-1">{{ $product->display_price }}</p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <label for="quantity" class="text-sm font-medium text-gray-700">{{ __('Quantity') }}</label>
            <div class="flex items-center border border-gray-300 rounded-md">
                <button
                    type="button"
                    wire:click="decrementQuantity"
                    aria-label="{{ __('Decrease quantity') }}"
                    class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                    @disabled($quantity <= 1)
                >
                    <x-heroicon-m-minus class="w-4 h-4" />
                </button>
                <input
                    id="quantity"
                    type="number"
                    wire:model.live="quantity"
                    min="1"
                    max="{{ $this->maxQuantity() }}"
                    class="w-14 border-0 text-center focus:ring-0 focus:border-0 p-0"
                />
                <button
                    type="button"
                    wire:click="incrementQuantity"
                    aria-label="{{ __('Increase quantity') }}"
                    class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                    @disabled($quantity >= $this->maxQuantity())
                >
                    <x-heroicon-m-plus class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-danger-light text-danger px-4 py-3 rounded mb-6">{{ session('error') }}</div>
    @endif

    @error('payment')
        <div class="bg-danger-light text-danger px-4 py-3 rounded mb-6">{{ $message }}</div>
    @enderror

    <form wire:submit="pay" class="bg-white rounded-lg shadow-md p-6 flex flex-col gap-6">
{{--        <div>--}}
{{--            <label for="coupon_code" class="block text-sm font-medium text-gray-700">{{ __('Coupon Code') }}</label>--}}
{{--            <div class="mt-1 flex gap-2">--}}
{{--                <x-input--}}
{{--                    name="coupon_code"--}}
{{--                    id="coupon_code"--}}
{{--                    variant="flat"--}}
{{--                    placeholder="{{ __('e.g. SAVE10') }}"--}}
{{--                    wire:model="couponCode"--}}
{{--                    class="flex-1"--}}
{{--                />--}}
{{--                <x-button--}}
{{--                    type="button"--}}
{{--                    size="sm"--}}
{{--                    wire:click="applyCoupon"--}}
{{--                    wire:loading.attr="disabled"--}}
{{--                    wire:target="applyCoupon"--}}
{{--                >--}}
{{--                    <span wire:loading.remove wire:target="applyCoupon">{{ __('Apply') }}</span>--}}
{{--                    <span wire:loading wire:target="applyCoupon">{{ __('...') }}</span>--}}
{{--                </x-button>--}}
{{--            </div>--}}
{{--            @if($appliedCode)--}}
{{--                <p class="mt-1 text-sm text-success">{{ __('Coupon ":code" applied!', ['code' => $appliedCode]) }}</p>--}}
{{--            @endif--}}
{{--            @error('couponCode')--}}
{{--                <p class="mt-1 text-sm text-danger">{{ $message }}</p>--}}
{{--            @enderror--}}
{{--        </div>--}}

        <div class="border-t border-gray-200 pt-4 flex flex-col gap-2" aria-live="polite">
            <div class="flex justify-between text-gray-600">
                <span>{{ __('Subtotal') }}</span>
                <span>{{ Str::price($subtotal, $product->currency_code) }}</span>
            </div>

            @if($discount > 0)
                <div class="flex justify-between text-success">
                    <span>{{ __('Discount') }}</span>
                    <span>-{{ Str::price($discount, $product->currency_code) }}</span>
                </div>
            @endif

            <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-2">
                <span>{{ __('Total') }}</span>
                <span>{{ Str::price($total, $product->currency_code) }}</span>
            </div>
        </div>

        <x-button
            type="submit"
            class="w-full"
            wire:loading.attr="disabled"
            wire:target="pay"
            id="pay-button"
        >
            <span wire:loading.remove wire:target="pay">{{ $total <= 0 ? __('Get Free') : __('Pay Now') }}</span>
            <span wire:loading wire:target="pay">{{ __('Processing...') }}</span>
        </x-button>
    </form>

    @if($total > 0)
        @push('scripts')
            <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const snapToken = '{{ session('snap_token') }}';

                    if (snapToken) {
                        window.snap.pay(snapToken, {
                            onSuccess: function () {
                                window.location.href = '{{ route("payment.success") }}';
                            },
                            onPending: function () {
                                window.location.href = '{{ route("payment.pending") }}';
                            },
                            onError: function () {
                                window.location.href = '{{ route("payment.error") }}';
                            },
                        });
                    }

                    window.addEventListener('snap-token-ready', function (event) {
                        window.snap.pay(event.detail.token, {
                            onSuccess: function () {
                                window.location.href = '{{ route("payment.success") }}';
                            },
                            onPending: function () {
                                window.location.href = '{{ route("payment.pending") }}';
                            },
                            onError: function () {
                                window.location.href = '{{ route("payment.error") }}';
                            },
                            onClose: function () {
                                document.getElementById('pay-button')?.removeAttribute('disabled');
                            },
                        });
                    });
                });
            </script>
        @endpush
    @endif
</div>
