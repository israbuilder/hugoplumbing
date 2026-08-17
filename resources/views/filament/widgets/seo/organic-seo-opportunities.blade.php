<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            SEO Conversion Opportunities
        </x-slot>

        <x-slot name="description">
            Pages with ranking, CTR or conversion
            opportunities detected from Search Console
            and GA4 together.
        </x-slot>

        @php
            $rows =
                $this->getRows();
        @endphp

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr
                        class="
                            border-b
                            border-gray-200
                            dark:border-white/10
                        "
                    >

                        <th class="px-3 py-3 text-left">
                            Page
                        </th>

                        <th class="px-3 py-3 text-left">
                            Opportunity
                        </th>

                        <th class="px-3 py-3 text-right">
                            Clicks
                        </th>

                        <th class="px-3 py-3 text-right">
                            Sessions
                        </th>

                        <th class="px-3 py-3 text-right">
                            Position
                        </th>

                        <th class="px-3 py-3 text-right">
                            Key Events
                        </th>

                        <th class="px-3 py-3 text-right">
                            Rate
                        </th>

                    </tr>

                </thead>

                <tbody
                    class="
                        divide-y
                        divide-gray-100
                        dark:divide-white/5
                    "
                >

                    @forelse(
                        $rows as $row
                    )

                        @php
                            $meta =
                                $this
                                    ->opportunityMeta(
                                        $row
                                            ->opportunity
                                    );
                        @endphp

                        <tr>

                            <td
                                class="
                                    px-3
                                    py-3
                                    font-medium
                                "
                            >
                                {{ $row->path }}
                            </td>

                            <td class="px-3 py-3">

                                <div
                                    class="
                                        flex
                                        flex-col
                                        gap-1
                                    "
                                >

                                    <div>
                                        <x-filament::badge
                                            :color="
                                                $meta[
                                                    'color'
                                                ]
                                            "
                                        >
                                            {{
                                                $row
                                                    ->opportunity
                                            }}
                                        </x-filament::badge>
                                    </div>

                                    <div
                                        class="
                                            max-w-lg
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        {{
                                            $meta[
                                                'description'
                                            ]
                                        }}
                                    </div>

                                </div>

                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row
                                            ->search_clicks
                                    )
                                }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row
                                            ->sessions
                                    )
                                }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row
                                            ->position,
                                        1
                                    )
                                }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row
                                            ->key_events,
                                        0
                                    )
                                }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row
                                            ->key_event_rate
                                        * 100,
                                        1
                                    )
                                }}%
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="
                                    px-3
                                    py-10
                                    text-center
                                    text-gray-500
                                "
                            >
                                No organic opportunities
                                detected for this period.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>