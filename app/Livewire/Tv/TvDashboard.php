<?php

namespace App\Livewire\Tv;

use App\Enums\DashboardSlideType;
use App\Enums\GoalType;
use App\Models\Announcement;
use App\Models\Dashboard;
use App\Models\DashboardSlide;
use App\Models\Sale;
use App\Models\SalesGoal;
use App\Services\Dashboard\SalesLeaderboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('components.layouts.tv')]
class TvDashboard extends Component
{
    public Dashboard $dashboard;

    public string $token;

    public array $slides = [];

    public string $lastUpdatedAt = '';

    public function mount(
        Dashboard $dashboard,
        string $token
    ): void {
        $this->validateDashboardAccess(
            dashboard: $dashboard,
            token: $token,
        );

        $this->dashboard = $dashboard;
        $this->token = $token;

        $this->loadDashboard();
    }

    public function refreshDashboard(): void
    {
        $this->dashboard->refresh();

        $this->validateDashboardAccess(
            dashboard: $this->dashboard,
            token: $this->token,
        );

        $this->loadDashboard();

        $this->dispatch('tv-dashboard-updated');
    }

    private function expandSlide(array $slide): array
{
    /*
     * Solamente dividimos slides que muestran vendedores.
     *
     * Puedes agregar/quitar tipos aquí dependiendo
     * de cuáles quieras paginar.
     */
    $splittableTypes = [
        DashboardSlideType::Race->value,
        DashboardSlideType::Leaderboard->value,
    ];

    if (! in_array($slide['type'], $splittableTypes, true)) {
        return [$slide];
    }

    $leaderboard = collect(
        $slide['leaderboard'] ?? []
    );

    if ($leaderboard->isEmpty()) {
        return [$slide];
    }

    /*
     * Máximo de vendedores por pantalla.
     *
     * Puedes permitir sobrescribirlo desde settings:
     *
     * {
     *     "vendors_per_slide": 3
     * }
     */
    $perSlide = max(
        1,
        (int) (
            $slide['settings']['vendors_per_slide']
            ?? 3
        )
    );

    /*
     * Si solamente hay 3 o menos,
     * dejamos el slide como estaba.
     */
    if ($leaderboard->count() <= $perSlide) {
        return [$slide];
    }

    $chunks = $leaderboard
        ->chunk($perSlide)
        ->values();

    $totalPages = $chunks->count();

    return $chunks
        ->map(function (
            Collection $chunk,
            int $page
        ) use (
            $slide,
            $totalPages
        ): array {

            return [
                ...$slide,

                /*
                 * Es MUY importante que cada slide tenga
                 * un ID diferente para Livewire.
                 */
                'id' => $slide['id']
                    . '-page-'
                    . ($page + 1),

                'leaderboard' => $chunk
                    ->values()
                    ->all(),

                'page' => $page + 1,

                'pages' => $totalPages,

                /*
                 * Opcional:
                 * agregar indicador al subtítulo.
                 */
                'subtitle' => $totalPages > 1
                    ? trim(
                        ($slide['subtitle'] ?? '')
                        . ' • '
                        . ($page + 1)
                        . '/'
                        . $totalPages
                    )
                    : ($slide['subtitle'] ?? null),
            ];
        })
        ->all();
}

    private function loadDashboard(): void
    {
        $dashboardSlides = DashboardSlide::query()
            ->with([
                'goal.team',
                'goal.participants',
                'team',
            ])
            ->where('dashboard_id', $this->dashboard->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

       $this->slides = $dashboardSlides
            ->flatMap(function (DashboardSlide $slide): array {

                $builtSlide = $this->buildSlide($slide);

                return $this->expandSlide($builtSlide);
            })
            ->values()
            ->all();

        $this->lastUpdatedAt = now(
            $this->dashboard->timezone
        )->format('g:i:s A');
    }

    private function buildSlide(
        DashboardSlide $slide
    ): array {
        $type = $slide->type instanceof DashboardSlideType
            ? $slide->type
            : DashboardSlideType::from($slide->type);

        $base = [
            'id' => $slide->id,
            'type' => $type->value,
            'name' => $slide->name,
            'title' => $slide->title
                ?: $this->defaultTitle($type),

            'subtitle' => $slide->subtitle,

            'duration' => max(
                3,
                $slide->duration_seconds
                    ?: $this->dashboard
                        ->default_slide_duration
            ),

            'settings' => $slide->settings ?? [],
        ];

        return match ($type) {
            DashboardSlideType::Race => [
                ...$base,
                ...$this->raceData($slide),
            ],

            DashboardSlideType::Leaderboard => [
                ...$base,
                ...$this->leaderboardData($slide),
            ],

            DashboardSlideType::DailySales => [
                ...$base,
                ...$this->dailySalesData($slide),
            ],

            DashboardSlideType::GoalProgress => [
                ...$base,
                ...$this->goalProgressData($slide),
            ],

            DashboardSlideType::TopPerformer => [
                ...$base,
                ...$this->topPerformerData($slide),
            ],

            DashboardSlideType::TeamComparison => [
                ...$base,
                ...$this->teamComparisonData($slide),
            ],

            DashboardSlideType::Announcement => [
                ...$base,
                ...$this->announcementData(),
            ],

            DashboardSlideType::Custom => [
                ...$base,
                'content' => $slide->settings['content']
                    ?? null,
            ],
        };
    }

    private function raceData(
        DashboardSlide $slide
    ): array {
        $goal = $this->resolveGoal($slide);

        if (! $goal) {
            return [
                'goal' => null,
                'leaderboard' => [],
            ];
        }

        return [
            'goal' => $this->serializeGoal($goal),

            'leaderboard' => app(
                SalesLeaderboardService::class
            )
                ->forGoal($goal)
                ->all(),
        ];
    }

    private function leaderboardData(
        DashboardSlide $slide
    ): array {
        return $this->raceData($slide);
    }

    private function goalProgressData(
        DashboardSlide $slide
    ): array {
        $goal = $this->resolveGoal($slide);

        if (! $goal) {
            return [
                'goal' => null,
                'leaderboard' => [],
                'total' => 0,
                'progress' => 0,
            ];
        }

        $leaderboard = app(
            SalesLeaderboardService::class
        )->forGoal($goal);

        $total = (float) $leaderboard
            ->sum('current_value');

        /*
         * En una meta general target_value representa
         * el total de la compañía.
         */
        $target = (float) $goal->target_value;

        $progress = $target > 0
            ? ($total / $target) * 100
            : 0;

        return [
            'goal' => $this->serializeGoal($goal),
            'leaderboard' => $leaderboard->all(),
            'total' => round($total, 2),
            'target' => round($target, 2),
            'progress' => round($progress, 2),
            'visual_progress' => min(
                max($progress, 0),
                100
            ),
        ];
    }

    private function dailySalesData(
        DashboardSlide $slide
    ): array {
        $timezone = $this->dashboard->timezone;

        $startsAt = now($timezone)
            ->startOfDay()
            ->utc();

        $endsAt = now($timezone)
            ->endOfDay()
            ->utc();

        $query = Sale::query()
            ->approved()
            ->whereBetween('sold_at', [
                $startsAt,
                $endsAt,
            ]);

        if ($slide->sales_team_id) {
            $query->whereHas(
                'salesperson',
                fn (Builder $query): Builder =>
                    $query->where(
                        'sales_team_id',
                        $slide->sales_team_id
                    )
            );
        }

        $sales = (clone $query)
            ->with('salesperson')
            ->latest('sold_at')
            ->limit(8)
            ->get();

        return [
            'total_sales' => (float) (
                clone $query
            )->sum('amount'),

            'sales_count' => (clone $query)->count(),

            'average_sale' => round(
                (float) (clone $query)->avg('amount'),
                2
            ),

            'recent_sales' => $sales
                ->map(fn (Sale $sale): array => [
                    'id' => $sale->id,
                    'salesperson' =>
                        $sale->salesperson->display_name
                        ?: $sale->salesperson->name,

                    'customer' => $sale->customer_name
                        ?: 'Cliente',

                    'amount' => (float) $sale->amount,
                    'currency' => $sale->currency,
                    'sold_at' => $sale->sold_at
                        ->timezone($timezone)
                        ->format('g:i A'),
                ])
                ->all(),
        ];
    }

    private function topPerformerData(
        DashboardSlide $slide
    ): array {
        $goal = $this->resolveGoal($slide);

        if (! $goal) {
            return [
                'goal' => null,
                'performer' => null,
            ];
        }

        $performer = app(
            SalesLeaderboardService::class
        )
            ->forGoal($goal)
            ->first();

        return [
            'goal' => $this->serializeGoal($goal),
            'performer' => $performer,
        ];
    }

    private function teamComparisonData(
        DashboardSlide $slide
    ): array {
        $goal = $this->resolveGoal($slide);

        if (! $goal) {
            return [
                'goal' => null,
                'teams' => [],
            ];
        }

        $teams = app(
            SalesLeaderboardService::class
        )
            ->forGoal($goal)
            ->groupBy(
                fn (array $row): string =>
                    $row['team'] ?: 'Sin equipo'
            )
            ->map(function (
                Collection $members,
                string $teamName
            ): array {
                return [
                    'name' => $teamName,
                    'color' => $members
                        ->first()['team_color']
                        ?? '#2563eb',

                    'total' => round(
                        (float) $members
                            ->sum('current_value'),
                        2
                    ),

                    'members' => $members->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        $highest = collect($teams)
            ->max('total') ?: 1;

        $teams = collect($teams)
            ->map(function (array $team) use (
                $highest
            ): array {
                return [
                    ...$team,
                    'visual_progress' => round(
                        ($team['total'] / $highest) * 100,
                        2
                    ),
                ];
            })
            ->all();

        return [
            'goal' => $this->serializeGoal($goal),
            'teams' => $teams,
        ];
    }

    private function announcementData(): array
    {
        $announcement = Announcement::query()
            ->active()
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('dashboard_id')
                    ->orWhere(
                        'dashboard_id',
                        $this->dashboard->id
                    );
            })
            ->orderBy('sort_order')
            ->latest()
            ->first();

        if (! $announcement) {
            return ['announcement' => null];
        }

        return [
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'message' => $announcement->message,
                'type' => $announcement->type,

                'image_url' => $announcement->image_path
                    ? asset(
                        'storage/'
                        . $announcement->image_path
                    )
                    : null,

                'video_url' => $announcement->video_url,
            ],
        ];
    }

    private function resolveGoal(
        DashboardSlide $slide
    ): ?SalesGoal {
        if ($slide->goal) {
            return $slide->goal;
        }

        return SalesGoal::query()
            ->with([
                'team',
                'participants',
            ])
            ->active()
            ->visibleOnDashboard()
            ->current()
            ->when(
                $slide->sales_team_id,
                fn (Builder $query): Builder =>
                    $query->where(
                        'sales_team_id',
                        $slide->sales_team_id
                    )
            )
            ->orderByDesc('is_primary')
            ->latest('starts_at')
            ->first();
    }

    private function serializeGoal(
        SalesGoal $goal
    ): array {
        $goalType = $goal->goal_type instanceof GoalType
            ? $goal->goal_type
            : GoalType::from($goal->goal_type);

        return [
            'id' => $goal->id,
            'name' => $goal->name,
            'type' => $goalType->value,
            'type_label' => $goalType->label(),
            'target_value' => (float) $goal->target_value,
            'currency' => $goal->currency,

            'starts_at' => $goal->starts_at
                ->timezone($this->dashboard->timezone)
                ->format('M j, Y'),

            'ends_at' => $goal->ends_at
                ->timezone($this->dashboard->timezone)
                ->format('M j, Y'),
        ];
    }

    private function defaultTitle(
        DashboardSlideType $type
    ): string {
        return match ($type) {
            DashboardSlideType::Race =>
                'Carrera de ventas',

            DashboardSlideType::Leaderboard =>
                'Ranking de vendedores',

            DashboardSlideType::DailySales =>
                'Resultados de hoy',

            DashboardSlideType::GoalProgress =>
                'Progreso de la meta',

            DashboardSlideType::TopPerformer =>
                'Vendedor destacado',

            DashboardSlideType::TeamComparison =>
                'Competencia por equipos',

            DashboardSlideType::Announcement =>
                'Anuncio',

            DashboardSlideType::Custom =>
                'Dashboard',
        };
    }

    private function validateDashboardAccess(
        Dashboard $dashboard,
        string $token
    ): void {
        if (
            ! $dashboard->is_active
            || ! hash_equals(
                (string) $dashboard->access_token,
                $token
            )
        ) {
            throw new NotFoundHttpException();
        }
    }

    public function render(): View
    {
        return view('livewire.tv.tv-dashboard');
    }
}