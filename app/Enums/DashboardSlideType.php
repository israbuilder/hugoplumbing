<?php

namespace App\Enums;

enum DashboardSlideType: string
{
    case Race = 'race';
    case Leaderboard = 'leaderboard';
    case DailySales = 'daily_sales';
    case GoalProgress = 'goal_progress';
    case TopPerformer = 'top_performer';
    case TeamComparison = 'team_comparison';
    case Announcement = 'announcement';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Race => 'Carrera',
            self::Leaderboard => 'Ranking',
            self::DailySales => 'Ventas del día',
            self::GoalProgress => 'Progreso de meta',
            self::TopPerformer => 'Mejor vendedor',
            self::TeamComparison => 'Comparación de equipos',
            self::Announcement => 'Anuncio',
            self::Custom => 'Personalizado',
        };
    }
}