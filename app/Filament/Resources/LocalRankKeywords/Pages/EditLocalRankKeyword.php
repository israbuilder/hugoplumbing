<?php

namespace App\Filament\Resources\LocalRankKeywords\Pages;

use App\Filament\Resources\LocalRankKeywords\LocalRankKeywordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLocalRankKeyword extends EditRecord
{
    protected static string $resource =
        LocalRankKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}