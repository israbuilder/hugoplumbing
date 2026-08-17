<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Top Landing Pages
        </x-slot>

        <x-slot name="description">
            Pages receiving organic traffic from Google.
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
                            Page
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

                        @php
                            $path =
                                parse_url(
                                    $row->page,
                                    PHP_URL_PATH
                                ) ?: '/';
                        @endphp

                        <tr>

                            <td
                                class="
                                    max-w-sm
                                    px-3
                                    py-3
                                "
                            >

                                <a
                                    href="{{ $row->page }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="
                                        font-medium
                                        text-primary-600
                                        hover:underline
                                    "
                                >
                                    {{ $path }}
                                </a>

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
                                No pages found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>