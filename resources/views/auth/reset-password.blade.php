<x-layouts.app>
    <div class="min-h-[80vh] flex items-center py-12 bg-gray-50">
        <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
            <div class="bg-white border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 font-display">{{ __('Reset Password') }}</h1>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-input
                        variant="flat"
                        type="email"
                        name="email"
                        :label="__('Email')"
                        value="{{ old('email', request()->email) }}"
                        required
                        autofocus
                    />

                    <x-input
                        variant="flat"
                        type="password"
                        name="password"
                        :label="__('New Password')"
                        required
                    />

                    <x-input
                        variant="flat"
                        type="password"
                        name="password_confirmation"
                        :label="__('Confirm New Password')"
                        required
                    />

                    <x-button type="submit" color="primary" variant="solid">
                        {{ __('Reset Password') }}
                    </x-button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
