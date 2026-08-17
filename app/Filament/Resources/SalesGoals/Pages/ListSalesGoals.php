<?php

namespace App\Filament\Resources\SalesGoals\Pages;

use App\Filament\Resources\SalesGoals\SalesGoalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesGoals extends ListRecords
{
    protected static string $resource = SalesGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
