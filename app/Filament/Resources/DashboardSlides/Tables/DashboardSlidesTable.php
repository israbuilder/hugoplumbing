<?php

namespace App\Filament\Resources\DashboardSlides\Tables;

use App\Enums\DashboardSlideType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DashboardSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Slide')
                    ->searchable(),

                TextColumn::make('dashboard.name')
                    ->label('Dashboard')
                    ->badge()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof DashboardSlideType
                                ? $state->label()
                                : ucfirst((string) $state)
                    ),

                TextColumn::make('goal.name')
                    ->label('Meta')
                    ->placeholder('Sin meta')
                    ->limit(25),

                TextColumn::make('duration_seconds')
                    ->label('Duración')
                    ->suffix(' s'),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('dashboard_id')
                    ->label('Dashboard')
                    ->relationship('dashboard', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->options([
                        'race' => 'Carrera',
                        'leaderboard' => 'Ranking',
                        'daily_sales' => 'Ventas del día',
                        'goal_progress' => 'Progreso de meta',
                        'top_performer' => 'Mejor vendedor',
                        'team_comparison' => 'Equipos',
                        'announcement' => 'Anuncio',
                        'custom' => 'Personalizado',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}