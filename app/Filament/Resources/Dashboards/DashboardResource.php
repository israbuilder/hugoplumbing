<?php

namespace App\Filament\Resources\Dashboards;

use App\Filament\Resources\Dashboards\Pages\CreateDashboard;
use App\Filament\Resources\Dashboards\Pages\EditDashboard;
use App\Filament\Resources\Dashboards\Pages\ListDashboards;
use App\Filament\Resources\Dashboards\Schemas\DashboardForm;
use App\Filament\Resources\Dashboards\Tables\DashboardsTable;
use App\Models\Dashboard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Dashboard';

    public static function form(Schema $schema): Schema
    {
        return DashboardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DashboardsTable::configure($table);
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
            'index' => ListDashboards::route('/'),
            'create' => CreateDashboard::route('/create'),
            'edit' => EditDashboard::route('/{record}/edit'),
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
