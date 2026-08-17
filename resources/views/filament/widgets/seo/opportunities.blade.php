<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            SEO Opportunities
        </x-slot>

        <x-slot name="description">
            Queries with meaningful impressions that
            are already close enough to improve quickly.
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
                            "
                        >
                            Keyword
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-left
                            "
                        >
                            Opportunity
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Clicks
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Impressions
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            CTR
                        </th>

                        <th
                            class="
                                px-3
                                py-3
                                text-right
                            "
                        >
                            Position
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

                        @php
                            $opportunity =
                                $this
                                    ->getOpportunity(
                                        $row
                                    );
                        @endphp

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
                                {{ $row->query }}
                            </td>

                            <td
                                class="
                                    px-3
                                    py-3
                                "
                            >

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
                                                $opportunity[
                                                    'color'
                                                ]
                                            "
                                        >
                                            {{
                                                $opportunity[
                                                    'label'
                                                ]
                                            }}
                                        </x-filament::badge>
                                    </div>

                                    <span
                                        class="
                                            max-w-md
                                            text-xs
                                            text-gray-500
                                            dark:text-gray-400
                                        "
                                    >
                                        {{
                                            $opportunity[
                                                'description'
                                            ]
                                        }}
                                    </span>

                                </div>

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
                                    font-semibold
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
                                colspan="6"
                                class="
                                    px-3
                                    py-10
                                    text-center
                                    text-gray-500
                                "
                            >
                                No SEO opportunities
                                found for this period.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>