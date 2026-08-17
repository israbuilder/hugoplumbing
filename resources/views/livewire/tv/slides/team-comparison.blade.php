<div
    class="tv-slide tv-slide-enter flex flex-col px-10 py-8"
>
    <div class="mb-8">
        <p
            class="text-sm font-black uppercase tracking-[0.28em] text-violet-300"
        >
            Competencia por equipos
        </p>

        <h1 class="mt-2 text-4xl font-black">
            {{ $slide['title'] }}
        </h1>
    </div>

    @if(count($slide['teams']))
        <div
            class="tv-glass flex min-h-0 flex-1 flex-col justify-center gap-7 rounded-3xl p-8"
        >
            @foreach($slide['teams'] as $index => $team)
                <div>
                    <div
                        class="mb-3 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <span
                                class="grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-xl font-black"
                            >
                                {{ $index + 1 }}
                            </span>

                            <div>
                                <p
                                    class="text-xl font-black"
                                >
                                    {{ $team['name'] }}
                                </p>

                                <p class="text-sm text-slate-400">
                                    {{ $team['members'] }}
                                    vendedores
                                </p>
                            </div>
                        </div>

                        <p
                            class="text-3xl font-black"
                        >
                            @if(
                                $slide['goal']['type']
                                === 'revenue'
                            )
                                {{ Number::currency(
                                    $team['total'],
                                    in: $slide['goal']['currency']
                                ) }}
                            @else
                                {{ Number::format(
                                    $team['total']
                                ) }}
                            @endif
                        </p>
                    </div>

                    <div
                        class="h-7 overflow-hidden rounded-full bg-white/10"
                    >
                        <div
                            class="tv-progress-bar h-full rounded-full"
                            style="
                                width: {{ max($team['visual_progress'], 3) }}%;
                                background-color: {{ $team['color'] }};
                            "
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        @include('livewire.tv.slides.partials.empty', [
            'message' => 'No existen equipos con ventas.',
        ])
    @endif
</div>