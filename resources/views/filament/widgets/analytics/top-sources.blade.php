<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Top Traffic Sources
        </x-slot>

        <x-slot name="description">
            Source / medium generating the most sessions.
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

                        <th
                            class="
                                px-3
                                py-3
                                text-left
                            "
                        >
                            Source / Medium
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Sessions
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Users
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Engagement
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Key Events
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
                                    px-3
                                    py-3
                                    font-medium
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                {{
                                    $row->source
                                }}

                                <span
                                    class="
                                        text-gray-400
                                    "
                                >
                                    /
                                </span>

                                {{
                                    $row->medium
                                }}
                            </td>

                            <td
                                class="
                                    px-3
                                    py-3
                                    text-right
                                "
                            >
                                {{
                                    number_format(
                                        $row
                                            ->sessions
                                    )
                                }}
                            </td>

                            <td
                                class="
                                    px-3
                                    py-3
                                    text-right
                                "
                            >
                                {{
                                    number_format(
                                        $row
                                            ->users
                                    )
                                }}
                            </td>

                            <td
                                class="
                                    px-3
                                    py-3
                                    text-right
                                "
                            >
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

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="
                                    px-3
                                    py-8
                                    text-center
                                    text-gray-500
                                "
                            >
                                No source data found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>