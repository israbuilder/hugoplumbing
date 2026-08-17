<div
    class="tv-slide tv-slide-enter grid place-items-center px-12 py-10"
>
    @if($slide['goal'])
        <div class="w-full max-w-6xl">
            <div class="text-center">
                <p
                    class="text-sm font-black uppercase tracking-[0.3em] text-sky-300"
                >
                    Objetivo empresarial
                </p>

                <h1 class="mt-3 text-5xl font-black">
                    {{ $slide['title'] }}
                </h1>

                <p class="mt-3 text-xl text-slate-400">
                    {{ $slide['goal']['name'] }}
                </p>
            </div>

            <div
                class="tv-glass mt-12 rounded-[2rem] p-10"
            >
                <div
                    class="flex items-end justify-between"
                >
                    <div>
                        <p class="text-slate-400">
                            Progreso acumulado
                        </p>

                        <p
                            class="mt-2 text-6xl font-black text-white"
                        >
                            @if(
                                $slide['goal']['type']
                                === 'revenue'
                            )
                                {{ Number::currency(
                                    $slide['total'],
                                    in: $slide['goal']['currency']
                                ) }}
                            @else
                                {{ Number::format(
                                    $slide['total']
                                ) }}
                            @endif
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-slate-400">
                            Meta
                        </p>

                        <p class="mt-2 text-3xl font-black">
                            @if(
                                $slide['goal']['type']
                                === 'revenue'
                            )
                                {{ Number::currency(
                                    $slide['target'],
                                    in: $slide['goal']['currency']
                                ) }}
                            @else
                                {{ Number::format(
                                    $slide['target']
                                ) }}
                            @endif
                        </p>
                    </div>
                </div>

                <div
                    class="mt-10 h-12 overflow-hidden rounded-full bg-white/10 p-1.5"
                >
                    <div
                        class="tv-progress-bar flex h-full items-center justify-end rounded-full bg-gradient-to-r from-sky-500 to-emerald-400 px-5"
                        style="width: {{ max($slide['visual_progress'], 4) }}%;"
                    >
                        <span
                            class="text-lg font-black text-slate-950"
                        >
                            {{ Number::format(
                                $slide['progress'],
                                precision: 1
                            ) }}%
                        </span>
                    </div>
                </div>

                <div
                    class="mt-8 grid grid-cols-3 gap-5"
                >
                    <div
                        class="rounded-2xl bg-white/5 p-5 text-center"
                    >
                        <p class="text-sm text-slate-400">
                            Participantes
                        </p>

                        <p class="mt-2 text-3xl font-black">
                            {{ count($slide['leaderboard']) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl bg-white/5 p-5 text-center"
                    >
                        <p class="text-sm text-slate-400">
                            Restante
                        </p>

                        <p class="mt-2 text-3xl font-black">
                            @php
                                $remaining = max(
                                    $slide['target']
                                    - $slide['total'],
                                    0
                                );
                            @endphp

                            @if(
                                $slide['goal']['type']
                                === 'revenue'
                            )
                                {{ Number::currency(
                                    $remaining,
                                    in: $slide['goal']['currency']
                                ) }}
                            @else
                                {{ Number::format($remaining) }}
                            @endif
                        </p>
                    </div>

                    <div
                        class="rounded-2xl bg-white/5 p-5 text-center"
                    >
                        <p class="text-sm text-slate-400">
                            Finaliza
                        </p>

                        <p class="mt-2 text-3xl font-black">
                            {{ $slide['goal']['ends_at'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('livewire.tv.slides.partials.empty', [
            'message' => 'No existe una meta actual.',
        ])
    @endif
</div>