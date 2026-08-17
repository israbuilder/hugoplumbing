<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Enums\SaleStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sold_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable()
                    ->placeholder('Sin referencia'),

                TextColumn::make('salesperson.name')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('Sin cliente'),

                TextColumn::make('amount')
                    ->label('Importe')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof SaleStatus
                                ? $state->label()
                                : ucfirst((string) $state)
                    )
                    ->color(fn ($state): string => match (
                        $state instanceof SaleStatus
                            ? $state->value
                            : $state
                    ) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('goal.name')
                    ->label('Meta')
                    ->limit(25)
                    ->placeholder('Sin meta'),

                TextColumn::make('source')
                    ->label('Origen')
                    ->badge(),

                TextColumn::make('sold_at')
                    ->label('Fecha')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('salesperson_id')
                    ->label('Vendedor')
                    ->relationship('salesperson', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('sales_goal_id')
                    ->label('Meta')
                    ->relationship('goal', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'cancelled' => 'Cancelada',
                        'refunded' => 'Reembolsada',
                    ]),

                Filter::make('today')
                    ->label('Ventas de hoy')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query->whereBetween('sold_at', [
                                now()->startOfDay(),
                                now()->endOfDay(),
                            ])
                    ),

                Filter::make('this_month')
                    ->label('Este mes')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query->whereBetween('sold_at', [
                                now()->startOfMonth(),
                                now()->endOfMonth(),
                            ])
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