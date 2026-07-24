<div class="min-h-[80vh] flex items-center py-12 bg-gray-50">
    <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
        <div class="bg-white border border-gray-200 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 font-display">{{ __('Set Your Password') }}</h1>
                <p class="mt-2 text-gray-500">{{ __('Welcome! Please create a password for your admin account.') }}</p>
            </div>

            <form wire:submit="submit" class="flex flex-col gap-6">
                <x-input
                    variant="flat"
                    type="password"
                    name="password"
                    :label="__('Password')"
                    wire:model.blur="password"
                    required
                    autofocus
                />

                <x-input
                    variant="flat"
                    type="password"
                    name="passwordConfirmation"
                    :label="__('Confirm Password')"
                    wire:model.blur="passwordConfirmation"
                    required
                />

                <x-button
                    type="submit"
                    color="primary"
                    variant="solid"
                    wire:loading.attr="disabled"
                    fullWidth
                >
                    <span wire:loading.remove>{{ __('Set Password') }}</span>
                    <span wire:loading>{{ __('Setting...') }}</span>
                </x-button>

                <p class="text-center text-sm text-gray-500">
                    <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark font-semibold">{{ __('Back to Login') }}</a>
                </p>
            </form>
        </div>
    </div>
</div>
