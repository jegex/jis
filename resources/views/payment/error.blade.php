<x-layouts.app>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <div class="bg-white rounded-lg shadow-md p-12">
            <div class="text-danger text-6xl mb-4">&#10007;</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Payment Failed') }}</h1>
            <p class="text-gray-600 mb-8">{{ __('Something went wrong with your payment. Please try again.') }}</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-dark">{{ __('Try Again') }}</a>
        </div>
    </div>
</x-layouts.app>
