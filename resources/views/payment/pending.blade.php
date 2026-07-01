<x-layouts.app>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <div class="bg-white rounded-lg shadow-md p-12">
            <div class="text-warning text-6xl mb-4">&#8987;</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Payment Pending') }}</h1>
            <p class="text-gray-600 mb-8">{{ __('Your payment is being processed. We will notify you once it is confirmed.') }}</p>
            <a href="{{ route('home') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-dark">{{ __('Back to Home') }}</a>
        </div>
    </div>
</x-layouts.app>
