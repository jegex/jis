<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('Profile') }}</h1>

    @if($message)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ $message }}
        </div>
    @endif

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Profile Information') }}</h2>
            <p class="text-sm text-gray-500 mb-6">{{ __('Update your name and email address.') }}</p>
            <form wire:submit="updateProfile" class="space-y-4">
                <div>
                    <x-input
                        variant="flat"
                        type="text"
                        name="name"
                        :label="__('Name')"
                        wire:model="name"
                    />
                </div>
                <div>
                    <x-input
                        variant="flat"
                        type="email"
                        name="email"
                        :label="__('Email')"
                        wire:model="email"
                    />
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-primary-dark transition-colors">
                    {{ __('Save') }}
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Update Password') }}</h2>
            <p class="text-sm text-gray-500 mb-6">{{ __('Ensure your account is using a strong password.') }}</p>
            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <x-input
                        variant="flat"
                        type="password"
                        name="current_password"
                        :label="__('Current Password')"
                        wire:model="current_password"
                    />
                </div>
                <div>
                    <x-input
                        variant="flat"
                        type="password"
                        name="password"
                        :label="__('New Password')"
                        wire:model="password"
                    />
                </div>
                <div>
                    <x-input
                        variant="flat"
                        type="password"
                        name="password_confirmation"
                        :label="__('Confirm Password')"
                        wire:model="password_confirmation"
                    />
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-primary-dark transition-colors">
                    {{ __('Save') }}
                </button>
            </form>
        </div>
    </div>
</div>
