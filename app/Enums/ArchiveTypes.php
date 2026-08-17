<?php

namespace App\Enums;

enum AchievementType: string
{
    case FirstSale = 'first_sale';
    case GoalReached = 'goal_reached';
    case TopPerformer = 'top_performer';
    case SalesStreak = 'sales_streak';
    case RecordBroken = 'record_broken';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::FirstSale => 'Primera venta',
            self::GoalReached => 'Meta alcanzada',
            self::TopPerformer => 'Mejor vendedor',
            self::SalesStreak => 'Racha de ventas',
            self::RecordBroken => 'Récord superado',
            self::Custom => 'Personalizado',
        };
    }
}