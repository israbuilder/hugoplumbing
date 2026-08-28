<?php

namespace App\Filament\Resources\LocalRankKeywords;

use App\Filament\Resources\LocalRankKeywords\Pages\CreateLocalRankKeyword;
use App\Filament\Resources\LocalRankKeywords\Pages\EditLocalRankKeyword;
use App\Filament\Resources\LocalRankKeywords\Pages\ListLocalRankKeywords;
use App\Filament\Resources\LocalRankKeywords\Schemas\LocalRankKeywordForm;
use App\Filament\Resources\LocalRankKeywords\Tables\LocalRankKeywordsTable;
use App\Models\LocalRankKeyword;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
use Filament\Support\Icons\Heroicon;

class LocalRankKeywordResource extends Resource
{
    protected static ?string $model = LocalRankKeyword::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MagnifyingGlass;


    protected static ?string $navigationLabel =
        'Add Keywords';

    protected static ?string $modelLabel =
        'LocalRankKeyword';

    protected static ?string $pluralModelLabel =
        'LocalRankKeywords';

    protected static string|UnitEnum|null  $navigationGroup = 'Local SEO';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
       return LocalRankKeywordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
       return LocalRankKeywordsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListLocalRankKeywords::route('/'),

            'create' =>
                CreateLocalRankKeyword::route(
                    '/create'
                ),

            'edit' =>
                EditLocalRankKeyword::route(
                    '/{record}/edit'
                ),
        ];
    }
}