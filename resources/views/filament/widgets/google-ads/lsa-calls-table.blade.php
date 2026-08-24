<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            LSA Phone Calls
        </x-slot>

        <x-slot name="description">
            Phone conversations, duration,
            lead status and recording availability.
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
                            Date
                        </th>

                        <th class="px-3 py-3 text-left">
                            Lead
                        </th>

                        <th class="px-3 py-3 text-right">
                            Duration
                        </th>

                        <th class="px-3 py-3 text-left">
                            Call Quality
                        </th>

                        <th class="px-3 py-3 text-left">
                            Lead Status
                        </th>

                        <th class="px-3 py-3 text-center">
                            Charged
                        </th>

                        <th class="px-3 py-3 text-left">
                            Service
                        </th>

                        <th class="px-3 py-3 text-center">
                            Recording
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
                        $rows as $call
                    )

                        @php
                            $quality =
                                $this
                                    ->quality(
                                        $call
                                            ->call_duration_millis
                                    );
                        @endphp

                        <tr>

                            <td
                                class="
                                    whitespace-nowrap
                                    px-3
                                    py-3
                                "
                            >
                                {{
                                    $call
                                        ->event_at
                                        ?->format(
                                            'M j, Y g:i A'
                                        )
                                    ?? '—'
                                }}
                            </td>


                            <td
                                class="
                                    px-3
                                    py-3
                                    font-medium
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                #{{ $call->lead?->lead_id }}
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
                                    $this
                                        ->duration(
                                            $call
                                                ->call_duration_millis
                                        )
                                }}
                            </td>


                            <td class="px-3 py-3">

                                <x-filament::badge
                                    :color="
                                        $quality[
                                            'color'
                                        ]
                                    "
                                >
                                    {{
                                        $quality[
                                            'label'
                                        ]
                                    }}
                                </x-filament::badge>

                            </td>


                            <td class="px-3 py-3">

                                {{
                                    ucwords(
                                        strtolower(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $call
                                                    ->lead
                                                    ?->lead_status
                                                ?? 'Unknown'
                                            )
                                        )
                                    )
                                }}

                            </td>


                            <td
                                class="
                                    px-3
                                    py-3
                                    text-center
                                "
                            >

                                @if(
                                    $call
                                        ->lead
                                        ?->lead_charged
                                )

                                    <x-filament::badge
                                        color="danger"
                                    >
                                        Yes
                                    </x-filament::badge>

                                @else

                                    <x-filament::badge
                                        color="gray"
                                    >
                                        No
                                    </x-filament::badge>

                                @endif

                            </td>


                            <td
                                class="
                                    px-3
                                    py-3
                                "
                            >
                                {{
                                    $call
                                        ->lead
                                        ?->service_id
                                    ?? '—'
                                }}
                            </td>


                            <td
                                class="
                                    px-3
                                    py-3
                                    text-center
                                "
                            >

                                @if(
                                    $call
                                        ->call_recording_url
                                )

                                    <a
                                        href="{{
                                            $call
                                                ->call_recording_url
                                        }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="
                                            font-medium
                                            text-primary-600
                                            hover:underline
                                        "
                                    >
                                        Open
                                    </a>

                                @else

                                    —

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="
                                    px-3
                                    py-10
                                    text-center
                                    text-gray-500
                                "
                            >
                                No LSA phone conversations
                                found for this period.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>