<div class="space-y-6" x-data="{ tab: 'raw' }">
    @php
        $emailService = app(\App\Services\EmailService::class);
        $variables = [
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'order_id' => '12345',
            'product_name' => 'Sample Product',
            'total' => 'Rp 100.000',
            'download_url' => 'https://example.com/download/sample',
            'site_name' => config('app.name'),
            'newsletter_content' => 'This is a sample newsletter content.',
        ];
    @endphp

    <div class="flex gap-2">
        <x-filament::button color="gray" @click="tab = 'raw'" x-bind:class="tab === 'raw' ? 'bg-gray-200' : ''">
            {{ __('Raw Content') }}
        </x-filament::button>
        <x-filament::button color="gray" @click="tab = 'rendered'" x-bind:class="tab === 'rendered' ? 'bg-gray-200' : ''">
            {{ __('Rendered Email') }}
        </x-filament::button>
    </div>

    <template x-if="tab === 'raw'">
        <div>
            @foreach (['en' => __('English'), 'id' => __('Indonesian')] as $locale => $localeName)
                <x-filament::section>
                    <h3 class="mb-2 text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $localeName }}</h3>

                    <div class="mb-3">
                        <span class="mb-2 font-medium ">{{ __('Subject:') }}</span>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                value="{{ $emailService->parseTemplate($record->getTranslation('subject', $locale), $variables) }}"
                                disabled
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <span class="mb-2 font-medium ">{{ __('Body:') }}</span>

                        <x-filament::input.wrapper class="fi-fo-textarea">
                            <textarea rows="10" disabled class="w-full">{!! $emailService->parseTemplate($record->getTranslation('body', $locale), $variables) !!}
                            </textarea>
                        </x-filament::input.wrapper>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </template>

    <template x-if="tab === 'rendered'">
        <div>
            @foreach (['en' => __('English'), 'id' => __('Indonesian')] as $locale => $localeName)
                @php
                    $subject = $emailService->parseTemplate($record->getTranslation('subject', $locale), $variables);
                    $body = $emailService->parseTemplate($record->getTranslation('body', $locale), $variables);
                @endphp
                <x-filament::section :label="$localeName">
                    <div class="mb-3">
                        <span class="text-sm font-medium text-gray-500">{{ __('Subject:') }}</span>
                        <p class="mt-1 text-gray-900">{{ $subject }}</p>
                    </div>

                    <div style="max-width: 100%; overflow: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <iframe srcdoc="{{ view('email.layout', ['subject' => $subject, 'body' => $body])->render() }}" style="width: 100%; height: 400px; border: 0;"></iframe>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </template>
</div>
