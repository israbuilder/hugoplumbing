<div
    x-data="tvDashboard"
    wire:poll.{{ max(2, $dashboard->refresh_interval) }}s="refreshDashboard"
    class="tv-background relative h-screen w-screen overflow-hidden"
>
    {{-- ============================================================
        BACKGROUND DECORATIONS
    ============================================================= --}}
    <div class="tv-grid-overlay pointer-events-none absolute inset-0"></div>

    <div class="tv-yellow-glow tv-yellow-glow-left"></div>
    <div class="tv-yellow-glow tv-yellow-glow-right"></div>

    {{-- ============================================================
        TOP CAUTION STRIPE
    ============================================================= --}}
    <div class="tv-caution-bar absolute inset-x-0 top-0 z-50 h-4"></div>

    {{-- ============================================================
        HEADER
    ============================================================= --}}
    <header class="tv-header absolute inset-x-0 top-4 z-40 h-24">

        {{-- LEFT --}}
        <div class="flex min-w-0 items-center gap-5">

            <div class="tv-logo-box">
                <img
                    src="{{ asset('logo-white.png') }}"
                    alt="{{ $dashboard->name }}"
                    class="max-h-12 max-w-44 object-contain"
                >
            </div>

            <div class="min-w-0">
                <div class="mb-1 flex items-center gap-3">
                    <span class="tv-live-badge">
                        Live
                    </span>

                    <span class="hidden text-xs font-black uppercase tracking-[0.25em] text-zinc-400 xl:inline">
                        Sales Competition
                    </span>
                </div>

                <h1 class="truncate text-2xl font-black uppercase italic tracking-tight text-white 2xl:text-3xl">
                    {{ $dashboard->name }}
                </h1>

                <p class="mt-0.5 text-xs font-bold uppercase tracking-[0.2em] text-yellow-300">
                    Sales Performance Center
                </p>
            </div>
        </div>

        {{-- CENTER --}}
        <div class="tv-header-goal hidden lg:block">
            <div class="tv-header-goal-inner">
                <span class="block text-[10px] font-black uppercase tracking-[0.32em]">
                    Race to
                </span>

                <span class="block text-2xl font-black italic leading-none xl:text-3xl">
                    THE FINISH
                </span>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex shrink-0 items-center gap-5">

            <div class="text-right">
                <p
                    x-text="clock"
                    class="text-2xl font-black tabular-nums text-yellow-300 xl:text-3xl"
                ></p>

                <p
                    x-text="date"
                    class="mt-1 text-xs font-bold capitalize tracking-wide text-zinc-400"
                ></p>
            </div>

            <button
                type="button"
                x-on:click="requestFullscreen"
                class="tv-fullscreen-button"
                aria-label="Pantalla completa"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                >
                    <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
                    <path d="M16 3h3a2 2 0 0 1 2 2v3"/>
                    <path d="M8 21H5a2 2 0 0 1-2-2v-3"/>
                    <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- ============================================================
        MAIN / SWIPER
    ============================================================= --}}
    <main class="absolute inset-x-0 bottom-16 top-28 z-10">

        @if(count($slides))

            <div
                x-ref="swiper"
                class="swiper h-full w-full"
            >
                <div class="swiper-wrapper">

                    @foreach($slides as $slide)

                        <section
                            wire:key="tv-slide-{{ $slide['id'] }}"
                            class="swiper-slide h-full"
                            data-swiper-autoplay="{{ $slide['duration'] * 1000 }}"
                        >
                            @include(
                                'livewire.tv.slides.' . $slide['type'],
                                [
                                    'slide' => $slide,
                                    'dashboard' => $dashboard,
                                ]
                            )
                        </section>

                    @endforeach

                </div>
            </div>

        @else

            <div class="grid h-full place-items-center p-10">

                <div class="tv-empty-panel">

                    <div class="text-6xl">
                        🏁
                    </div>

                    <h2 class="mt-6 text-3xl font-black uppercase italic text-white">
                        No hay slides activos
                    </h2>

                    <p class="mt-3 text-zinc-400">
                        Crea y activa slides desde Filament.
                    </p>

                </div>

            </div>

        @endif
    </main>

    {{-- ============================================================
        FOOTER
    ============================================================= --}}
    <footer class="tv-footer absolute inset-x-0 bottom-0 z-40 h-16">

        {{-- LEFT --}}
        <div class="flex items-center gap-4">

            <div class="tv-live-footer">
                <span class="relative flex h-2.5 w-2.5">

                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-black opacity-50"
                    ></span>

                    <span
                        class="relative inline-flex h-2.5 w-2.5 rounded-full bg-black"
                    ></span>

                </span>

                LIVE
            </div>

            <span class="hidden text-sm font-semibold text-zinc-400 sm:inline">
                Actualizado:

                <strong class="ml-1 text-white">
                    {{ $lastUpdatedAt }}
                </strong>
            </span>

        </div>

        {{-- CENTER --}}
        <div class="tv-slide-counter">

            <span class="text-lg">
                🏁
            </span>

            <span class="font-black tabular-nums text-yellow-300">
                <span x-text="currentSlide + 1"></span>
                /
                {{ max(count($slides), 1) }}
            </span>

        </div>

        {{-- RIGHT --}}
        <div class="flex items-center gap-2">

            <button
                type="button"
                x-on:click="previous"
                class="tv-control-button"
            >
                ←
                <span class="hidden xl:inline">
                    Anterior
                </span>
            </button>

            <button
                type="button"
                x-on:click="toggleAutoplay"
                class="tv-control-button tv-control-button-primary"
            >
                Pausar
            </button>

            <button
                type="button"
                x-on:click="next"
                class="tv-control-button"
            >
                <span class="hidden xl:inline">
                    Siguiente
                </span>
                →
            </button>

        </div>

    </footer>
</div>