<?php

namespace App\Filament\Resources\SalesTeams;

use App\Filament\Resources\SalesTeams\Pages\CreateSalesTeam;
use App\Filament\Resources\SalesTeams\Pages\EditSalesTeam;
use App\Filament\Resources\SalesTeams\Pages\ListSalesTeams;
use App\Filament\Resources\SalesTeams\Schemas\SalesTeamForm;
use App\Filament\Resources\SalesTeams\Tables\SalesTeamsTable;
use App\Models\SalesTeam;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesTeamResource extends Resource
{
    protected static ?string $model = SalesTeam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null  $navigationGroup = 'Sales';

      protected static ?string $navigationLabel = 'Sales Team';

    protected static ?string $modelLabel = 'equipo';

    protected static ?string $pluralModelLabel = 'equipos';

    protected static ?int $navigationSort = 10;
    
    public static function form(Schema $schema): Schema
    {
        return SalesTeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesTeamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesTeams::route('/'),
            'create' => CreateSalesTeam::route('/create'),
            'edit' => EditSalesTeam::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
