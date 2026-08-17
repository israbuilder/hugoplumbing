<?php

namespace App\Filament\Resources\SalesGoals;

use App\Filament\Resources\SalesGoals\Pages\CreateSalesGoal;
use App\Filament\Resources\SalesGoals\Pages\EditSalesGoal;
use App\Filament\Resources\SalesGoals\Pages\ListSalesGoals;
use App\Filament\Resources\SalesGoals\Schemas\SalesGoalForm;
use App\Filament\Resources\SalesGoals\Tables\SalesGoalsTable;
use App\Models\SalesGoal;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesGoalResource extends Resource
{
    protected static ?string $model = SalesGoal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null  $navigationGroup = 'Ventas';

     protected static ?string $navigationLabel = 'Metas';

    protected static ?string $modelLabel = 'meta';

    protected static ?string $pluralModelLabel = 'metas';

    protected static ?int $navigationSort = 30;
    
    public static function form(Schema $schema): Schema
    {
        return SalesGoalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesGoalsTable::configure($table);
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
            'index' => ListSalesGoals::route('/'),
            'create' => CreateSalesGoal::route('/create'),
            'edit' => EditSalesGoal::route('/{record}/edit'),
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
