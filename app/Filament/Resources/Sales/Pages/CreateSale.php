<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

      protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (
            ($data['status'] ?? null) === SaleStatus::Approved->value
            && empty($data['approved_at'])
        ) {
            $data['approved_at'] = now();
        }

        return $data;
    }
}
