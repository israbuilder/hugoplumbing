<div
    class="tv-slide tv-slide-enter grid place-items-center px-10 py-8"
>
    @if($slide['performer'])
        <div class="w-full max-w-5xl text-center">
            <p
                class="text-sm font-black uppercase tracking-[0.35em] text-amber-300"
            >
                ⭐ Top performer
            </p>

            <h1 class="mt-3 text-5xl font-black">
                {{ $slide['title'] }}
            </h1>

            <div
                class="tv-glass relative mt-10 overflow-hidden rounded-[2.5rem] px-12 py-10"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-gradient-to-br from-amber-400/10 via-transparent to-sky-400/10"
                ></div>

                <div class="relative">
                    <div
                        class="mx-auto grid h-40 w-40 place-items-center overflow-hidden rounded-full border-8 border-amber-300 bg-slate-800 text-5xl font-black shadow-2xl"
                    >
                        @if($slide['performer']['avatar_url'])
                            <img
                                src="{{ $slide['performer']['avatar_url'] }}"
                                alt="{{ $slide['performer']['name'] }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ $slide['performer']['initials'] }}
                        @endif
                    </div>

                    <p
                        class="mt-7 text-4xl font-black"
                    >
                        {{ $slide['performer']['name'] }}
                    </p>

                    <p class="mt-2 text-lg text-slate-400">
                        {{ $slide['performer']['team'] ?: 'Equipo general' }}
                    </p>

                    <p
                        class="mt-7 text-6xl font-black text-amber-300"
                    >
                        @if(
                            $slide['goal']['type']
                            === 'revenue'
                        )
                            {{ Number::currency(
                                $slide['performer']['current_value'],
                                in: $slide['goal']['currency']
                            ) }}
                        @else
                            {{ Number::format(
                                $slide['performer']['current_value']
                            ) }}
                        @endif
                    </p>

                    <p
                        class="mt-3 text-xl font-bold text-sky-300"
                    >
                        {{ Number::format(
                            $slide['performer']['progress'],
                            precision: 1
                        ) }}% de su meta
                    </p>
                </div>
            </div>
        </div>
    @else
        @include('livewire.tv.slides.partials.empty', [
            'message' => 'No existe información para seleccionar al mejor vendedor.',
        ])
    @endif
</div>