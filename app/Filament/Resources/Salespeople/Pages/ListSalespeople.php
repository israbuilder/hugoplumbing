<?php

namespace App\Filament\Resources\Salespeople\Pages;

use App\Filament\Resources\Salespeople\SalespersonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalespeople extends ListRecords
{
    protected static string $resource = SalespersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
