<x-layouts.app>
    <div class="min-h-[80vh] flex items-center py-12 bg-gray-50">
        <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
            <div class="bg-white border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 font-display">{{ __('Reset Password') }}</h1>
                </div>

                <form method="POST" action="{{ route('password.update') }}" id="reset-password-form" class="flex flex-col gap-6">
                    @csrf

                    @error('g-recaptcha-response')
                        <div class="bg-red-50 border border-red-200 text-red-600 p-4 rounded-lg text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
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

    @if (config('services.recaptcha.enabled'))
        @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script>
            document.getElementById('reset-password-form').addEventListener('submit', function (event) {
                event.preventDefault();
                const form = this;
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'reset-password' }).then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    });
                });
            });
        </script>
        @endpush
    @endif
</x-layouts.app>
