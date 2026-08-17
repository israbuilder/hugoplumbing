<?php

namespace App\Services\Dashboard;

use App\Enums\GoalType;
use App\Models\SalesGoal;
use App\Models\SalesGoalParticipant;
use App\Models\Salesperson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesLeaderboardService
{
    public function forGoal(SalesGoal $goal): Collection
    {
        $salespeople = $this->resolveSalespeople($goal);

        return $salespeople
            ->map(function (Salesperson $salesperson) use ($goal): array {
                $participant = $goal->participants
                    ->firstWhere(
                        'salesperson_id',
                        $salesperson->getKey()
                    );

                $startingValue = (float) (
                    $participant?->starting_value ?? 0
                );

                $currentValue = $this->currentValue(
                    salesperson: $salesperson,
                    goal: $goal,
                ) + $startingValue;

                $targetValue = (float) (
                    $participant?->target_value
                    ?? $goal->target_value
                );

                $progress = $targetValue > 0
                    ? ($currentValue / $targetValue) * 100
                    : 0;

                return [
                    'salesperson_id' => $salesperson->getKey(),
                    'name' => $salesperson->display_name
                        ?: $salesperson->name,

                    'avatar_url' => $salesperson->avatar_path
                        ? asset('storage/' . $salesperson->avatar_path)
                        : null,

                    'photo_url' => $salesperson->photo_path
                        ? asset('storage/' . $salesperson->photo_path)
                        : null,

                    'initials' => $this->initials(
                        $salesperson->display_name
                            ?: $salesperson->name
                    ),

                    'avatar_color' => $salesperson->avatar_color
                        ?: '#2563eb',

                    'avatar_animation' =>
                        $salesperson->avatar_animation
                        ?: 'runner',

                    'team' => $salesperson->team?->name,
                    'team_color' => $salesperson->team?->color,

                    'current_value' => round($currentValue, 2),
                    'target_value' => round($targetValue, 2),
                    'progress' => round($progress, 2),

                    /*
                     * Se limita a 96 para que el avatar no salga
                     * visualmente de la pista.
                     */
                    'visual_progress' => min(
                        max($progress, 2),
                        96
                    ),

                    'goal_reached' => $targetValue > 0
                        && $currentValue >= $targetValue,
                ];
            })
            ->sortByDesc('current_value')
            ->values()
            ->map(function (array $row, int $index): array {
                return [
                    ...$row,
                    'rank' => $index + 1,
                ];
            });
    }

    private function resolveSalespeople(
        SalesGoal $goal
    ): Collection {
        $participantIds = $goal->participants
            ->where('is_active', true)
            ->pluck('salesperson_id');

        $query = Salesperson::query()
            ->with('team')
            ->where('status', 'active')
            ->where('show_on_dashboard', true);

        if ($participantIds->isNotEmpty()) {
            $query->whereIn('id', $participantIds);
        } elseif ($goal->sales_team_id) {
            $query->where(
                'sales_team_id',
                $goal->sales_team_id
            );
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function currentValue(
        Salesperson $salesperson,
        SalesGoal $goal
    ): float {
        $query = $salesperson
            ->sales()
            ->approved()
            ->between(
                $goal->starts_at,
                $goal->ends_at
            );

        return match ($goal->goal_type) {
            GoalType::Revenue =>
                (float) $query->sum('amount'),

            GoalType::SalesCount =>
                (float) $query->count(),

            GoalType::Calls =>
                (float) $query->sum('calls_count'),

            GoalType::Appointments =>
                (float) $query->sum(
                    'appointments_count'
                ),

            GoalType::Contracts =>
                (float) $query->sum(
                    'contracts_count'
                ),

            GoalType::Points =>
                (float) $query->sum('points'),
        };
    }

    private function initials(string $name): string
    {
        return str($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(
                fn (string $word): string =>
                    mb_strtoupper(
                        mb_substr($word, 0, 1)
                    )
            )
            ->implode('');
    }
}