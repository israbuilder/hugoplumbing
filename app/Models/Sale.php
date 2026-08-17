<?php

namespace App\Models;

use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'salesperson_id',
        'sales_goal_id',
        'created_by',
        'reference_number',
        'source',
        'external_id',
        'customer_name',
        'description',
        'amount',
        'currency',
        'points',
        'calls_count',
        'appointments_count',
        'contracts_count',
        'status',
        'sold_at',
        'approved_at',
        'cancelled_at',
        'refunded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'points' => 'decimal:2',
            'calls_count' => 'integer',
            'appointments_count' => 'integer',
            'contracts_count' => 'integer',
            'status' => SaleStatus::class,
            'sold_at' => 'datetime',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(
            SalesGoal::class,
            'sales_goal_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function achievement(): HasOne
    {
        return $this->hasOne(Achievement::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(
            'status',
            SaleStatus::Approved->value
        );
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            SaleStatus::Pending->value
        );
    }

    public function scopeBetween(
        Builder $query,
        mixed $startsAt,
        mixed $endsAt
    ): Builder {
        return $query->whereBetween(
            'sold_at',
            [$startsAt, $endsAt]
        );
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereBetween('sold_at', [
            now()->startOfDay(),
            now()->endOfDay(),
        ]);
    }

    public function approve(): void
    {
        $this->forceFill([
            'status' => SaleStatus::Approved,
            'approved_at' => now(),
            'cancelled_at' => null,
            'refunded_at' => null,
        ])->save();
    }

    public function cancel(): void
    {
        $this->forceFill([
            'status' => SaleStatus::Cancelled,
            'cancelled_at' => now(),
        ])->save();
    }

    public function refund(): void
    {
        $this->forceFill([
            'status' => SaleStatus::Refunded,
            'refunded_at' => now(),
        ])->save();
    }
}