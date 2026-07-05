@props(['data' => [], 'items' => []])

@php
    $locale = app()->getLocale();
    $projects = $items;
    $projectTypes = $projects->isNotEmpty() ? $projects->toArray() : [];
    $typeKeys = array_keys($projectTypes);
    $allKey = 'all';
    $allProjects = $projects->flatten(1)->sortByDesc('date');

    $locale = app()->getLocale();
    $sectionLabel = $data['label'] ?? null;
    $sectionTitle = $data['title'] ?? null;
    $sectionDescription = $data['description'] ?? null;
    $showViewAll = $data['show_view_all'] ?? false;
@endphp

<section id="projects" class="border-b border-b-gray-200">
    <div class="container pt-16 pr-0! border-x border-x-gray-200 bg-radial-[45%_100%_at_65%_80%] from-red-200 to-white">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <x-sections.section-header
                    :label="locale_text($sectionLabel, $locale)"
                    :title="locale_text($sectionTitle, $locale)"
                    :description="locale_text($sectionDescription, $locale)"
                />
            </div>
            <div class="lg:col-span-3">
                @if($projects->isNotEmpty())
                    <div x-data="{ activeTab: '{{ $allKey }}' }" class="space-y-6" style="--primary-700: var(--color-red-500)">
                        <div class="inline-flex w-full" data-animate="fade-down">
                            <div class="flex flex-wrap gap-1 rounded-full bg-white/60 p-1 shadow-none" role="tablist">
                                <button type="button" role="tab" x-on:click="activeTab = '{{ $allKey }}'" x-bind:class="activeTab === '{{ $allKey }}' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap outline-none transition-colors">{{ __('Semua') }}</button>
                                @foreach($projectTypes as $type => $typeProjects)
                                    <button type="button" role="tab" x-on:click="activeTab = '{{ $type }}'" x-bind:class="activeTab === '{{ $type }}' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap outline-none transition-colors">{{ $type }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="border border-b-0 border-white/50 bg-red-200/50 rounded-tl-2xl pl-2 pt-2" data-animate="fade-up">
                            <div x-show="activeTab == '{{ $allKey }}'" class="tab-panel">
                                <div class="rounded-tl-xl border-t border-l border-gray-200 bg-white overflow-hidden" data-stagger>
                                    <div class="overflow-x-auto h-[350px]">
                                        <table class="w-full text-sm">
                                            <thead class="sticky top-0 bg-gray-50 z-10">
                                            <tr class="text-left">
                                                <th class="px-5 py-3.5 font-semibold text-gray-700 w-16">{{ __('No') }}</th>
                                                <th class="px-5 py-3.5 font-semibold text-gray-700">{{ __('Ship Name') }}</th>
                                                <th class="px-5 py-3.5 font-semibold text-gray-700">{{ __('Size') }}</th>
                                                <th class="px-5 py-3.5 font-semibold text-gray-700 w-24">{{ __('Year') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                            @foreach($allProjects as $project)
                                                @php
                                                    $project = is_array($project) ? $project : (method_exists($project, 'toArray') ? $project->toArray() : []);
                                                    $number = count($allProjects) - $loop->index;
                                                    $number = $number <= 9 ? "0$number" : $number;
                                                @endphp
                                                <tr class="hover:bg-primary-light/50 transition-colors">
                                                    <td class="px-5 py-3.5 text-gray-400">{{ $number }}</td>
                                                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $project['name'] ?? '' }}</td>
                                                    <td class="px-5 py-3.5 text-gray-500">{{ isset($project['size']) && $project['size'] ? number_format((float) $project['size'], 0, ',', '.') : '' }}{{ isset($project['unit']) && $project['unit'] ? ' ' . $project['unit'] : '' }}</td>
                                                    <td class="px-5 py-3.5 text-gray-500">{{ isset($project['date']) && $project['date'] ? \Carbon\Carbon::parse($project['date'])->format('Y') : '-' }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @foreach($projectTypes as $type => $typeProjects)
                                <div x-show="activeTab == '{{ $type }}'" class="tab-panel">
                                    <div class="rounded-tl-xl border-t border-l border-gray-200 bg-white overflow-hidden" data-stagger>
                                        <div class="overflow-x-auto h-[350px]">
                                            <table class="w-full text-sm">
                                                <thead class="sticky top-0 bg-gray-50 z-10">
                                                <tr class="text-left">
                                                    <th class="px-5 py-3.5 font-semibold text-gray-700 w-[64px]">{{ __('No') }}</th>
                                                    <th class="px-5 py-3.5 font-semibold text-gray-700">{{ __('Ship Name') }}</th>
                                                    <th class="px-5 py-3.5 font-semibold text-gray-700">{{ __('Size') }}</th>
                                                    <th class="px-5 py-3.5 font-semibold text-gray-700 w-[80px]">{{ __('Year') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                @foreach($typeProjects as $project)
                                                    @php
                                                        $project = is_array($project) ? $project : (method_exists($project, 'toArray') ? $project->toArray() : []);
                                                        $number = count($typeProjects) - $loop->index;
                                                        $number = $number <= 9 ? "0$number" : $number;
                                                    @endphp
                                                    <tr class="hover:bg-primary-light/50 transition-colors">
                                                        <td class="px-5 py-3.5 text-gray-400">{{ $number }}</td>
                                                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ $project['name'] ?? '' }}</td>
                                                        <td class="px-5 py-3.5 text-gray-500">{{ isset($project['size']) && $project['size'] ? number_format((float) $project['size'], 0, ',', '.') : '' }}{{ isset($project['unit']) && $project['unit'] ? ' ' . $project['unit'] : '' }}</td>
                                                        <td class="px-5 py-3.5 text-gray-500">{{ isset($project['date']) && $project['date'] ? \Carbon\Carbon::parse($project['date'])->format('Y') : '-' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-center h-64 text-gray-400"><p>{{ __('No projects data yet.') }}</p></div>
                @endif
            </div>
        </div>
    </div>
</section>
