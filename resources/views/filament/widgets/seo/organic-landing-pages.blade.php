<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Organic Landing Pages
        </x-slot>

        <x-slot name="description">
            Search visibility combined with organic
            sessions, engagement and key events.
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

                        <th class="px-3 py-3 text-right">
                            Clicks
                        </th>

                        <th class="px-3 py-3 text-right">
                            Impr.
                        </th>

                        <th class="px-3 py-3 text-right">
                            CTR
                        </th>

                        <th class="px-3 py-3 text-right">
                            Pos.
                        </th>

                        <th class="px-3 py-3 text-right">
                            Sessions
                        </th>

                        <th class="px-3 py-3 text-right">
                            Engage.
                        </th>

                        <th class="px-3 py-3 text-right">
                            Key Events
                        </th>

                        <th class="px-3 py-3 text-right">
                            Event Rate
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

                        <tr>

                            <td
                                class="
                                    max-w-sm
                                    px-3
                                    py-3
                                    font-medium
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                {{ $row->path }}
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
                                            ->impressions
                                    )
                                }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row->ctr
                                        * 100,
                                        2
                                    )
                                }}%
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
                                            ->sessions
                                    )
                                }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                {{
                                    number_format(
                                        $row
                                            ->engagement_rate
                                        * 100,
                                        1
                                    )
                                }}%
                            </td>

                            <td
                                class="
                                    px-3
                                    py-3
                                    text-right
                                    font-semibold
                                "
                            >
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
                                colspan="9"
                                class="
                                    px-3
                                    py-10
                                    text-center
                                    text-gray-500
                                "
                            >
                                No matching organic data
                                found for this period.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>