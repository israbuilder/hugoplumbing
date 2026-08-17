<?php

namespace App\Enums;

enum GoalPeriod: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Diaria',
            self::Weekly => 'Semanal',
            self::Monthly => 'Mensual',
            self::Quarterly => 'Trimestral',
            self::Yearly => 'Anual',
            self::Custom => 'Personalizada',
        };
    }
}