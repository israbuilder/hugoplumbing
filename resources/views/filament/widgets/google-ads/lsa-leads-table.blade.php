<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Local Services Leads
        </x-slot>

        <x-slot name="description">
            Individual leads retrieved directly
            from Google Local Services Ads.
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

                        <th class="px-3 py-3 text-left">
                            Type
                        </th>

                        <th class="px-3 py-3 text-left">
                            Status
                        </th>

                        <th class="px-3 py-3 text-left">
                            Service
                        </th>

                        <th class="px-3 py-3 text-center">
                            Charged
                        </th>

                        <th class="px-3 py-3 text-left">
                            Credit
                        </th>

                        <th class="px-3 py-3 text-right">
                            Conversations
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
                        $rows as $lead
                    )

                        <tr>

                            <td
                                class="
                                    whitespace-nowrap
                                    px-3
                                    py-3
                                "
                            >
                                {{
                                    $lead
                                        ->lead_created_at
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

                                #{{ $lead->lead_id }}

                            </td>


                            <td class="px-3 py-3">

                                <x-filament::badge
                                    :color="
                                        $this
                                            ->typeColor(
                                                $lead
                                                    ->lead_type
                                            )
                                    "
                                >
                                    {{
                                        ucwords(
                                            strtolower(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $lead
                                                        ->lead_type
                                                    ?? 'Unknown'
                                                )
                                            )
                                        )
                                    }}
                                </x-filament::badge>

                            </td>


                            <td class="px-3 py-3">

                                <x-filament::badge
                                    :color="
                                        $this
                                            ->statusColor(
                                                $lead
                                                    ->lead_status
                                            )
                                    "
                                >
                                    {{
                                        ucwords(
                                            strtolower(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $lead
                                                        ->lead_status
                                                    ?? 'Unknown'
                                                )
                                            )
                                        )
                                    }}
                                </x-filament::badge>

                            </td>


                            <td
                                class="
                                    max-w-xs
                                    px-3
                                    py-3
                                "
                            >
                                {{
                                    $lead
                                        ->service_id
                                    ?? $lead
                                        ->category_id
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
                                    $lead
                                        ->lead_charged
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


                            <td class="px-3 py-3">

                                @if(
                                    $lead
                                        ->credit_state
                                )

                                    <x-filament::badge
                                        :color="
                                            $this
                                                ->creditColor(
                                                    $lead
                                                        ->credit_state
                                                )
                                        "
                                    >
                                        {{
                                            ucwords(
                                                strtolower(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $lead
                                                            ->credit_state
                                                    )
                                                )
                                            )
                                        }}
                                    </x-filament::badge>

                                @else

                                    —

                                @endif

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
                                        $lead
                                            ->conversations_count
                                    )
                                }}
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
                                No LSA leads found
                                for this period.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-widgets::widget>