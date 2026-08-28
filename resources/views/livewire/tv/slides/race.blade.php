@php
    $goal = $slide['goal'] ?? null;

    $goalValue = (float) ($goal['target_value'] ?? 0);

    $isRevenue = ($goal['type'] ?? null) === 'revenue';

    $goalStarts = $goal['starts_at'];

    $goalEnds = $goal['ends_at'];

    $currency = $goal['currency'] ?? 'USD';

    /*
    |--------------------------------------------------------------------------
    | Escala de la carrera
    |--------------------------------------------------------------------------
    |
    | Divide la meta en 10 segmentos.
    |
    | Ejemplo:
    | $1,000,000
    |
    | 100K 200K 300K ... 900K 1M
    |
    */
    $scaleSteps = [];

    if ($goalValue > 0) {
        for ($i = 1; $i <= 10; $i++) {
            $value = ($goalValue / 10) * $i;

            if ($isRevenue) {
                if ($value >= 1000000) {
                    $label = '$' . rtrim(
                        rtrim(
                            number_format($value / 1000000, 1),
                            '0'
                        ),
                        '.'
                    ) . 'M';
                } elseif ($value >= 1000) {
                    $label = '$' . number_format(
                        $value / 1000,
                        0
                    ) . 'K';
                } else {
                    $label = '$' . number_format($value, 0);
                }
            } else {
                $label = Number::format($value);
            }

            $scaleSteps[] = $label;
        }
    }
@endphp


<div class="tv-slide tv-slide-enter race-slide">

    {{-- ============================================================
        SLIDE HEADER
    ============================================================= --}}
    <div class="race-slide-header">

        <div class="min-w-0">

            <div class="flex items-center gap-3">

                <span class="race-live-label">
                    Race
                </span>

                <span class="text-xs font-black uppercase tracking-[0.3em] text-yellow-300">
                    Competencia en vivo
                </span>

            </div>

            <h1 class="race-title">
                {{ $slide['title'] }} {{$goalStarts}} to {{$goalEnds}}
            </h1>

            @if($slide['subtitle'])
                <p class="race-subtitle">
                    {{ $slide['subtitle'] }}
                </p>
            @endif

           

        </div>


        @if($goal)

            <div class="race-goal-card">

                <div class="race-goal-card-accent"></div>

                <div>
                    <p class="race-goal-label">
                        Meta
                    </p>

                    <p class="race-goal-value">
                        @if($isRevenue)

                            {{ Number::currency(
                                $goalValue,
                                in: $currency
                            ) }}

                        @else

                            {{ Number::format(
                                $goalValue
                            ) }}

                        @endif
                    </p>
                </div>

                <div class="race-goal-flag">
                    🏁
                </div>

            </div>

        @endif

    </div>


    {{-- ============================================================
        BOARD
    ============================================================= --}}
    @if(count($slide['leaderboard']))

        <div class="race-board">

            {{-- ================================================
                TOP HAZARD DECORATION
            ================================================= --}}
            <div class="race-board-hazard"></div>


            {{-- ================================================
                SCALE
            ================================================= --}}
            <div class="race-scale">

                <div class="race-scale-start">
                    START
                </div>

                <div class="race-scale-values">

                    @foreach($scaleSteps as $index => $scaleLabel)

                        <div
                            @class([
                                'race-scale-item',
                                'race-scale-item-finish' => $loop->last,
                            ])
                        >
                            {{ $scaleLabel }}
                        </div>

                    @endforeach

                </div>

                <div class="race-scale-finish">
                    🏁
                </div>

            </div>


            {{-- ================================================
                MAIN RACE AREA
            ================================================= --}}
            <div class="race-area">

                {{-- Asphalt texture --}}
                <div class="race-asphalt"></div>

                {{-- Vertical yellow lines --}}
                <div class="race-distance-grid">

                    @for($i = 1; $i <= 9; $i++)

                        <div
                            class="race-distance-line"
                            style="left: {{ $i * 10 }}%;"
                        ></div>

                    @endfor

                </div>


                {{-- ==========================================
                    FINISH ZONE
                =========================================== --}}
                <div class="race-finish-zone">

                    <div class="race-checkered"></div>

                    <div class="race-million-sign">

                        <span class="race-million-lightning">
                            ⚡
                        </span>

                        <span class="race-million-text">
                            @if($isRevenue && $goalValue > 0)

                                @if($goalValue >= 1000000)
                                    {{ rtrim(
                                        rtrim(
                                            number_format(
                                                $goalValue / 1000000,
                                                1
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    ) }} MILLION
                                @else
                                    FINISH
                                @endif

                            @else
                                FINISH
                            @endif
                        </span>

                    </div>

                </div>


                {{-- ==========================================
                    LANES
                =========================================== --}}
                <div
                    class="race-lanes"
                    style="--lane-count: {{ max(count($slide['leaderboard']), 1) }};"
                >

                    @foreach($slide['leaderboard'] as $runner)

                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | Posición
                            |--------------------------------------------------------------------------
                            |
                            | Tu backend ya entrega visual_progress.
                            |
                            | Aquí solamente lo protegemos para garantizar
                            | que nunca pueda salir de 0 - 100.
                            |
                            */

                            $visualProgress = max(
                                0,
                                min(
                                    100,
                                    (float) $runner['visual_progress']
                                )
                            );
                        @endphp


                        <div
                            class="race-lane"
                            wire:key="runner-{{ $slide['id'] }}-{{ $runner['salesperson_id'] }}"
                        >

                            {{-- horizontal white line --}}
                            <div class="race-lane-border"></div>

                            {{-- road dashes --}}
                            <div class="race-road-dashes"></div>


                            {{-- ======================================
                                LEFT VALUE
                            ======================================= --}}
                            <div class="race-person-info">

                                <div class="race-rank">
                                    {{ $runner['rank'] }}
                                </div>

                                <div class="race-person-data">

                                    <p class="race-person-name">
                                        {{ $runner['name'] }}
                                    </p>

                                    <p class="race-person-team">
                                        {{ $runner['team'] ?: 'Equipo general' }}
                                    </p>

                                    <p class="race-person-money">

                                        @if($isRevenue)

                                            {{ Number::currency(
                                                $runner['current_value'],
                                                in: $currency
                                            ) }}

                                        @else

                                            {{ Number::format(
                                                $runner['current_value']
                                            ) }}

                                        @endif

                                    </p>

                                </div>

                            </div>


                            {{-- ======================================
                                RUNNER COURSE

                                IMPORTANT:
                                Esta zona comienza DESPUÉS del texto
                                de la izquierda y termina ANTES de la
                                bandera.

                                El 0% y 100% se calculan dentro de
                                esta zona.
                            ======================================= --}}
                            <div class="race-runner-course">

                                <div
                                    class="race-runner-position"
                                    style="left: {{ $visualProgress }}%;"
                                >

                                    {{-- Value floating above runner --}}
                                    <div class="race-runner-value">

                                        @if($isRevenue)

                                            {{ Number::currency(
                                                $runner['current_value'],
                                                in: $currency
                                            ) }}

                                        @else

                                            {{ Number::format(
                                                $runner['current_value']
                                            ) }}

                                        @endif

                                    </div>


                                    {{-- Goal --}}
                                    @if($runner['goal_reached'])

                                        <div class="race-goal-celebration-label">
                                            🏁 ¡META!
                                        </div>

                                    @endif


                                    {{-- ==================================
                                        HUMAN CHARACTER
                                    =================================== --}}
                                    <div class="race-character">

                                        {{-- HEAD --}}
                                        <div
                                            @class([
                                                'race-avatar',
                                                $runner['avatar_animation'],
                                                'goal-celebration' => $runner['goal_reached'],
                                            ])
                                            style="background-color: {{ $runner['avatar_color'] }};"
                                        >

                                            @if($runner['avatar_url'])

                                                <img
                                                    src="{{ $runner['avatar_url'] }}"
                                                    alt="{{ $runner['name'] }}"
                                                    class="h-full w-full object-cover"
                                                >

                                            @else

                                                <span class="text-xl font-black text-white">
                                                    {{ $runner['initials'] }}
                                                </span>

                                            @endif

                                        </div>


                                        {{-- BODY --}}
                                        <div class="race-body">

                                            <div class="race-body-torso"></div>

                                            <div class="race-arm race-arm-left"></div>

                                            <div class="race-arm race-arm-right"></div>

                                            <div class="race-leg race-leg-left"></div>

                                            <div class="race-leg race-leg-right"></div>

                                        </div>


                                        {{-- percentage --}}
                                        <div class="race-character-progress">

                                            {{ Number::format(
                                                $runner['progress'],
                                                precision: 1
                                            ) }}%

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- ==========================================
                    UPDATE SIGN
                =========================================== --}}
                <div class="race-update-sign">

                    <span>
                        UPDATE
                    </span>

                    <strong>
                        {{ now()->format('m-d-y') }}
                    </strong>

                </div>

            </div>


            {{-- ================================================
                BOTTOM BAR
            ================================================= --}}
            <div class="race-bottom">

                <div class="race-bottom-hazard"></div>

                <span class="race-bottom-text">
                    {{ now()->format('F j, Y') }}
                </span>

                <div class="race-bottom-hazard"></div>

            </div>

        </div>

    @else

        @include('livewire.tv.slides.partials.empty', [
            'message' => 'No hay vendedores asignados a esta meta.',
        ])

    @endif

</div>