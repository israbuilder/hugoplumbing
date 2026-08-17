<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobada',
            self::Cancelled => 'Cancelada',
            self::Refunded => 'Reembolsada',
        };
    }
}