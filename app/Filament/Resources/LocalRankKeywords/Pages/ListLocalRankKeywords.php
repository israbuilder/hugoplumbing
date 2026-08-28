<?php

namespace App\Filament\Resources\LocalRankKeywords\Pages;

use App\Filament\Resources\LocalRankKeywords\LocalRankKeywordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLocalRankKeywords extends ListRecords
{
    protected static string $resource =
        LocalRankKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Keyword'),
        ];
    }
}