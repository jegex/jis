<x-filament-panels::page>
    @php
        $record = $this->getRecord();
        $revisions = $this->getRevisions();
        $left = $this->getLeftRevision();
        $right = $this->getRightRevision();
        $changes = $this->getChanges();

        $eventLabels = [
            'created' => __('Created'),
            'updated' => __('Updated'),
            'deleted' => __('Deleted'),
            'restored' => __('Restored'),
        ];


    @endphp

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <x-filament::section>
                <x-slot name="heading">
                    {{ $record?->title ?? $record?->getKey() }}
                </x-slot>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-500">
                            {{ __('From') }}
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="leftId">
                                @foreach ($revisions as $revision)
                                    <option value="{{ $revision->id }}">
                                        {{ $eventLabels[$revision->event] ?? $revision->event }}
                                        &middot; {{ $revision->created_at->diffForHumans() }}
                                    </option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">
                            {{ __('To') }}
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="rightId">
                                @foreach ($revisions as $revision)
                                    <option value="{{ $revision->id }}">
                                        {{ $eventLabels[$revision->event] ?? $revision->event }}
                                        &middot; {{ $revision->created_at->diffForHumans() }}
                                    </option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </x-filament::section>
        </div>

        @if ($revisions->isEmpty())
            <x-filament::section>
                <div class="py-8 text-center text-gray-500">
                    {{ __('No revisions have been recorded yet.') }}
                </div>
            </x-filament::section>
        @else
            @if ($left !== null && $right !== null)
                <div class="flex items-center justify-between gap-4 mb-4">
                    <x-filament::button outlined wire:click="prev" icon="heroicon-m-chevron-left" :disabled="!$this->hasPrev()">
                        {{ __('Previous') }}
                    </x-filament::button>

                    <x-filament::button outlined wire:click="next" icon="heroicon-m-chevron-right" icon-position="after" :disabled="!$this->hasNext()">
                        {{ __('Next') }}
                    </x-filament::button>
                </div>

                <x-filament::section>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <x-filament::avatar
                                :src="$right?->causer?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($right?->causer?->name ?? 'S') . '&format=svg&color=FFFFFF&background=%2309090b'"
                                :alt="$right?->causer?->name ?? __('System')"
                                size="lg"
                            />

                            <div class="text-sm font-medium text-gray-900">
                                    {{ __('Revision by :name', ['name' => $right?->causer?->name ?? __('System')]) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $right?->created_at?->diffForHumans() }}
                                    ({{ $right?->created_at?->format('d M Y @ H:i') }})
                                </div>
                            </div>

                        <x-filament::button
                            color="danger"
                            wire:click="restoreRevision"
                            wire:confirm="{{ __('Restore this revision? The current content will be overwritten.') }}"
                            :disabled="!$this->canRestore()"
                        >
                            {{ __('Restore this revision') }}
                        </x-filament::button>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    @if ($changes === [])
                        <div class="py-4 text-center text-gray-500">
                            {{ __('These revisions have the same content.') }}
                        </div>
                    @else
                        @foreach ($changes as $field => $change)
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-900">
                                    {{ Str::title(str_replace('_', ' ', $field)) }}
                                </h4>

                                <div class="diff-wrapper">
                                    @php
                                        $rendererOptions = [
                                            'detailLevel' => 'line',
                                            'lineNumbers' => false,
                                        ];
                                        $jsonResult = \Jfcherng\Diff\DiffHelper::calculate($change['old'], $change['new'], 'Json');
                                        $htmlRenderer = \Jfcherng\Diff\Factory\RendererFactory::make('SideBySide', $rendererOptions);
                                    @endphp

                                    {!! $htmlRenderer->renderArray(json_decode($jsonResult, true)) !!}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </x-filament::section>
            @endif
        @endif


        <style>
            .diff-wrapper.diff {
                --tab-size: 4;
                border-collapse: collapse;
                border-spacing: 0;
                empty-cells: show;
                font-family: monospace;
                width: 100%;
                word-break: break-all;
            }
            .diff-wrapper.diff th {
                font-weight: 700;
                cursor: default;
                -webkit-user-select: none;
                user-select: none;
                text-align: center;
            }
            .diff-wrapper.diff td {
                vertical-align: baseline;
            }
            .diff-wrapper.diff td,
            .diff-wrapper.diff th {
                border-collapse: separate;
                border: none;
                padding: 6px 10px;
            }
            .diff-wrapper.diff td:empty:after,
            .diff-wrapper.diff th:empty:after {
                content: " ";
                visibility: hidden;
            }
            .diff-wrapper.diff td a,
            .diff-wrapper.diff th a {
                color: #000;
                cursor: inherit;
                pointer-events: none;
            }
            .diff-wrapper.diff thead th {
                padding: 8px;
                text-align: center;
            }
            .diff-wrapper.diff tbody.skipped {
                border-top: 1px solid black;
            }
            .diff-wrapper.diff tbody.skipped td,
            .diff-wrapper.diff tbody.skipped th {
                display: none;
            }
            .diff-wrapper.diff tbody th {
                background: #cccccc;
                border-right: 1px solid black;
                text-align: right;
                vertical-align: top;
                width: 4em;
            }
            .diff-wrapper.diff tbody th.sign {
                background: #fff;
                border-right: none;
                padding: 1px 0;
                text-align: center;
                width: 1em;
            }
            .diff-wrapper.diff tbody th.sign.del {
                background: #fbe1e1;
            }
            .diff-wrapper.diff tbody th.sign.ins {
                background: #e1fbe1;
            }
            .diff-wrapper.diff.diff-html {
                white-space: pre-wrap;
                tab-size: var(--tab-size);
            }
            .diff-wrapper.diff.diff-html .ch {
                line-height: 1em;
                background-clip: border-box;
                background-repeat: repeat-x;
                background-position: left center;
            }
            .diff-wrapper.diff.diff-html .ch.sp {
                background-image: url("data:image/svg+xml,%3Csvg preserveAspectRatio="xMinYMid meet" viewBox="0 0 12 24" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M4.5 11C4.5 10.1716 5.17157 9.5 6 9.5C6.82843 9.5 7.5 10.1716 7.5 11C7.5 11.8284 6.82843 12.5 6 12.5C5.17157 12.5 4.5 11.8284 4.5 11Z" fill="rgba%2860, 60, 60, 50%25%29"/%3E%3C/svg%3E");
                background-size: 1ch 1.25em;
            }
            .diff-wrapper.diff.diff-html .ch.tab {
                background-image: url("data:image/svg+xml,%3Csvg preserveAspectRatio="xMinYMid meet" viewBox="0 0 12 24" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M9.5 10.44L6.62 8.12L7.32 7.26L12.04 11V11.44L7.28 14.9L6.62 13.9L9.48 11.78H0V10.44H9.5Z" fill="rgba%2860, 60, 60, 50%25%29"/%3E%3C/svg%3E");
                background-size: calc(var(--tab-size) * 1ch) 1.25em;
                background-position: 2px center;
            }
            .diff-wrapper.diff.diff-html .change.change-eq .old,
            .diff-wrapper.diff.diff-html .change.change-eq .new {
                background: #fff;
            }
            .diff-wrapper.diff.diff-html .change .old {
                background-color: color-mix(in oklab, var(--danger-400) 10%, transparent);
            }
            .diff-wrapper.diff.diff-html .change .new {
                background-color: color-mix(in oklab, var(--success-400) 10%, transparent);
            }
            .diff-wrapper.diff.diff-html .change .new ins {
                color: var(--success-900);
            }
            .diff-wrapper.diff.diff-html .change .old ins {
                color: var(--danger-900);
            }
            .diff-wrapper.diff.diff-html .change .rep {
                background: #fef6d9;
            }
            .diff-wrapper.diff.diff-html .change .old.none,
            .diff-wrapper.diff.diff-html .change .new.none,
            .diff-wrapper.diff.diff-html .change .rep.none {
                background: transparent;
                cursor: not-allowed;
            }
            .diff-wrapper.diff.diff-html .change ins,
            .diff-wrapper.diff.diff-html .change del {
                font-weight: bold;
                text-decoration: none;
            }
            .diff-wrapper.diff.diff-html .change ins {
                background: #94f094;
            }
            .diff-wrapper.diff.diff-html .change del {
                background: color-mix(in oklab, var(--danger-400) 50%, transparent);
            }
        </style>
    </div>

</x-filament-panels::page>
