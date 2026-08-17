<?php

namespace App\Filament\Resources\SalesTeams\Pages;

use App\Filament\Resources\SalesTeams\SalesTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesTeams extends ListRecords
{
    protected static string $resource = SalesTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
