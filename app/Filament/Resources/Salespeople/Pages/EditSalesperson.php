<?php

namespace App\Filament\Resources\Salespeople\Pages;

use App\Filament\Resources\Salespeople\SalespersonResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesperson extends EditRecord
{
    protected static string $resource = SalespersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
