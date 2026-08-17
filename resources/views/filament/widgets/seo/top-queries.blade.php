<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Top Queries
        </x-slot>

        <x-slot name="description">
            Google searches generating the most organic clicks.
        </x-slot>

        @php
            $rows = $this->getRows();
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
                                font-medium
                            "
                        >
                            Query
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                                font-medium
                            "
                        >
                            Clicks
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                                font-medium
                            "
                        >
                            Impr.
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                                font-medium
                            "
                        >
                            CTR
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                                font-medium
                            "
                        >
                            Pos.
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

                    @forelse($rows as $row)

                        <tr>

                            <td
                                class="
                                    max-w-xs
                                    px-3
                                    py-3
                                    font-medium
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                {{ $row->query }}
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
                                        $row->clicks
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
                                        $row->impressions
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
                                        $row->ctr * 100,
                                        2
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
                                        $row->position,
                                        1
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
                                No Search Console data
                                found for this period.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>