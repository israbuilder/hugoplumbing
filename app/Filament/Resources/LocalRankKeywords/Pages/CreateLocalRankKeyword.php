<?php

namespace App\Filament\Resources\LocalRankKeywords\Pages;

use App\Filament\Resources\LocalRankKeywords\LocalRankKeywordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLocalRankKeyword extends CreateRecord
{
    protected static string $resource =
        LocalRankKeywordResource::class;
}