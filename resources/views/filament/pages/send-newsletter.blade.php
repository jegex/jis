<x-filament-panels::page>
    {{ $this->form }}

    <div class="flex justify-end">
        <x-filament::button
            wire:click="send"
            color="primary"
            icon="heroicon-o-paper-airplane"
        >
            {{ __('Send Newsletter') }}
        </x-filament::button>
    </div>

    @if (! empty($preview))
        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-4">{{ __('Preview') }}</h2>

            <x-filament::section>
                <div class="mb-3">
                    <span class="mb-2 font-medium ">{{ __('Subject:') }}</span>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            value="{{ $preview['subject'] }}"
                            disabled
                        />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <span class="mb-2 font-medium ">{{ __('Body:') }}</span>

                    <x-filament::input.wrapper class="fi-fo-textarea">
                    <textarea rows="10" disabled class="w-full">{!! $preview['body'] !!}
                    </textarea>
                    </x-filament::input.wrapper>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
