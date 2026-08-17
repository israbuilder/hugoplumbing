<?php

namespace App\Filament\Resources\Salespeople\Pages;

use App\Filament\Resources\Salespeople\SalespersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesperson extends CreateRecord
{
    protected static string $resource = SalespersonResource::class;
}
