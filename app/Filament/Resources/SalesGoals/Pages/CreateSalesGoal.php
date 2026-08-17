<?php

namespace App\Filament\Resources\SalesGoals\Pages;

use App\Filament\Resources\SalesGoals\SalesGoalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesGoal extends CreateRecord
{
    protected static string $resource = SalesGoalResource::class;

    protected function afterCreate(): void
{
    if (! $this->record->is_primary) {
        return;
    }

    \App\Models\SalesGoal::query()
        ->whereKeyNot($this->record->getKey())
        ->where('is_primary', true)
        ->update([
            'is_primary' => false,
        ]);
}
}
