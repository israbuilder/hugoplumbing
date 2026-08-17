<?php

namespace App\Filament\Resources\SalesGoals\Pages;

use App\Filament\Resources\SalesGoals\SalesGoalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesGoal extends EditRecord
{
    protected static string $resource = SalesGoalResource::class;

    protected function afterSave(): void
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
