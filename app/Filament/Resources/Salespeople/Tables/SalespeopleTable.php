<?php

namespace App\Filament\Resources\Salespeople\Tables;

use App\Enums\SalesPersonStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SalespeopleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(
                        fn ($record): string =>
                            'https://ui-avatars.com/api/?name='
                            . urlencode($record->name)
                    ),

                TextColumn::make('name')
                    ->label('Vendedor')
                    ->description(fn ($record) => $record->email)
                    ->searchable(['name', 'email', 'employee_number'])
                    ->sortable(),

                TextColumn::make('team.name')
                    ->label('Equipo')
                    ->badge()
                    ->sortable()
                    ->placeholder('Sin equipo'),

                ColorColumn::make('avatar_color')
                    ->label('Color'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof SalesPersonStatus
                                ? $state->label()
                                : ucfirst((string) $state)
                    )
                    ->color(fn ($state): string => match (
                        $state instanceof SalesPersonStatus
                            ? $state->value
                            : $state
                    ) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('show_on_dashboard')
                    ->label('Dashboard')
                    ->boolean(),

                TextColumn::make('sales_count')
                    ->label('Ventas')
                    ->counts('sales')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sales_team_id')
                    ->label('Equipo')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'suspended' => 'Suspendido',
                    ]),

                SelectFilter::make('show_on_dashboard')
                    ->label('Visible en dashboard')
                    ->options([
                        1 => 'Sí',
                        0 => 'No',
                    ]),

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