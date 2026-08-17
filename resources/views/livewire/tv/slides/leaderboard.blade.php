<div
    class="tv-slide tv-slide-enter flex flex-col px-10 py-8"
>
    <div class="mb-7">
        <p
            class="text-sm font-black uppercase tracking-[0.28em] text-amber-300"
        >
            Clasificación
        </p>

        <h1 class="mt-2 text-4xl font-black">
            {{ $slide['title'] }}
        </h1>

        @if($slide['subtitle'])
            <p class="mt-2 text-lg text-slate-400">
                {{ $slide['subtitle'] }}
            </p>
        @endif
    </div>

    @if(count($slide['leaderboard']))
        <div
            class="grid min-h-0 flex-1 grid-cols-12 gap-6"
        >
            @php
                $topThree = collect(
                    $slide['leaderboard']
                )->take(3);

                $remaining = collect(
                    $slide['leaderboard']
                )->skip(3);
            @endphp

            <div
                class="col-span-7 grid grid-cols-3 items-end gap-5"
            >
                @foreach($topThree as $person)
                    <div
                        @class([
                            'tv-glass rounded-3xl p-6 text-center',
                            'podium-first' => $person['rank'] === 1,
                            'min-h-[78%]' => $person['rank'] === 1,
                            'min-h-[66%]' => $person['rank'] === 2,
                            'min-h-[58%]' => $person['rank'] === 3,
                        ])
                    >
                        <div
                            @class([
                                'mx-auto grid h-20 w-20 place-items-center overflow-hidden rounded-full border-4 bg-slate-800 text-xl font-black',
                                'border-amber-300' => $person['rank'] === 1,
                                'border-slate-300' => $person['rank'] === 2,
                                'border-orange-400' => $person['rank'] === 3,
                            ])
                        >
                            @if($person['avatar_url'])
                                <img
                                    src="{{ $person['avatar_url'] }}"
                                    alt="{{ $person['name'] }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                {{ $person['initials'] }}
                            @endif
                        </div>

                        <div
                            class="mx-auto mt-4 grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-2xl font-black"
                        >
                            {{ $person['rank'] }}
                        </div>

                        <p
                            class="mt-4 truncate text-xl font-black"
                        >
                            {{ $person['name'] }}
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            {{ $person['team'] ?: 'General' }}
                        </p>

                        <p
                            class="mt-5 text-3xl font-black text-sky-300"
                        >
                            @if(
                                $slide['goal']['type']
                                === 'revenue'
                            )
                                {{ Number::currency(
                                    $person['current_value'],
                                    in: $slide['goal']['currency']
                                ) }}
                            @else
                                {{ Number::format(
                                    $person['current_value']
                                ) }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

            <div
                class="col-span-5 flex min-h-0 flex-col gap-3"
            >
                @forelse($remaining as $person)
                    <div
                        class="tv-glass flex items-center gap-4 rounded-2xl px-5 py-4"
                    >
                        <span
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white/10 text-xl font-black"
                        >
                            {{ $person['rank'] }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-lg font-black"
                            >
                                {{ $person['name'] }}
                            </p>

                            <p class="text-sm text-slate-400">
                                {{ $person['team'] ?: 'General' }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-lg font-black">
                                @if(
                                    $slide['goal']['type']
                                    === 'revenue'
                                )
                                    {{ Number::currency(
                                        $person['current_value'],
                                        in: $slide['goal']['currency']
                                    ) }}
                                @else
                                    {{ Number::format(
                                        $person['current_value']
                                    ) }}
                                @endif
                            </p>

                            <p class="text-sm text-sky-300">
                                {{ Number::format(
                                    $person['progress'],
                                    precision: 1
                                ) }}%
                            </p>
                        </div>
                    </div>
                @empty
                    <div
                        class="tv-glass grid flex-1 place-items-center rounded-3xl p-8 text-center text-slate-400"
                    >
                        Top 3 de vendedores
                    </div>
                @endforelse
            </div>
        </div>
    @else
        @include('livewire.tv.slides.partials.empty', [
            'message' => 'No hay información para mostrar.',
        ])
    @endif
</div>