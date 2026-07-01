<x-layouts.app>
    <div class="min-h-[80vh] flex items-center py-12 bg-gray-50">
        <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
            <div class="bg-white border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 font-display mb-2">{{ __('Verify Your Email') }}</h1>
                <p class="text-gray-500 mb-2">{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}</p>
                <p class="text-gray-500 mb-6">{{ __('If you did not receive the email, we will gladly send you another.') }}</p>

                @if (session('status') == 'verification-link-sent')
                    <div class="bg-success-light text-success p-4 rounded-lg mb-6 text-sm font-medium">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div class="flex flex-col items-center gap-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-button type="submit" color="primary" variant="solid">
                            {{ __('Resend Verification Email') }}
                        </x-button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 underline transition-colors">{{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
