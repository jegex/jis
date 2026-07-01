<x-layouts.app>
    <div class="min-h-[80vh] flex items-center py-12 bg-gray-50">
        <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
            <div class="bg-white border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 font-display">{{ __('Forgot Password') }}</h1>
                    <p class="mt-2 text-gray-500">{{ __('Enter your email address and we will send you a password reset link.') }}</p>
                </div>

                @if (session('status'))
                    <div class="bg-success-light text-success p-4 rounded-lg mb-6 text-sm font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
                    @csrf

                    <x-input
                        variant="flat"
                        type="email"
                        name="email"
                        :label="__('Email')"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    />

                    <x-button type="submit" color="primary" variant="solid">
                        {{ __('Send Password Reset Link') }}
                    </x-button>

                    <p class="text-center text-sm text-gray-500">
                        <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark font-semibold">{{ __('Back to Login') }}</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
