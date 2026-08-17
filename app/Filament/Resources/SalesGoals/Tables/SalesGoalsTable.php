<?php

namespace App\Filament\Resources\SalesGoals\Tables;

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesGoalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Meta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('team.name')
                    ->label('Equipo')
                    ->badge()
                    ->placeholder('General'),

                TextColumn::make('goal_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof GoalType
                                ? $state->label()
                                : ucfirst((string) $state)
                    ),

                TextColumn::make('target_value')
                    ->label('Objetivo')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),

                TextColumn::make('period')
                    ->label('Periodo')
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof GoalPeriod
                                ? $state->label()
                                : ucfirst((string) $state)
                    ),

                TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Final')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('participants_count')
                    ->label('Participantes')
                    ->counts('participants'),

                IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('goal_type')
                    ->label('Tipo')
                    ->options([
                        'revenue' => 'Ingresos',
                        'sales_count' => 'Ventas',
                        'calls' => 'Llamadas',
                        'appointments' => 'Citas',
                        'contracts' => 'Contratos',
                        'points' => 'Puntos',
                    ]),

                SelectFilter::make('sales_team_id')
                    ->label('Equipo')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('current')
                    ->label('Metas actuales')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query
                                ->where('starts_at', '<=', now())
                                ->where('ends_at', '>=', now())
                    ),

                Filter::make('primary')
                    ->label('Meta principal')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query->where('is_primary', true)
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}