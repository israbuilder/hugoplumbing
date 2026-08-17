<?php

namespace App\Enums;

enum GoalType: string
{
    case Revenue = 'revenue';
    case SalesCount = 'sales_count';
    case Calls = 'calls';
    case Appointments = 'appointments';
    case Contracts = 'contracts';
    case Points = 'points';

    public function label(): string
    {
        return match ($this) {
            self::Revenue => 'Ingresos',
            self::SalesCount => 'Número de ventas',
            self::Calls => 'Llamadas',
            self::Appointments => 'Citas',
            self::Contracts => 'Contratos',
            self::Points => 'Puntos',
        };
    }
}