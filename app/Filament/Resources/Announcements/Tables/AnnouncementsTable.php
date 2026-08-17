<?php

namespace App\Filament\Resources\Announcements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->square(),

                TextColumn::make('title')
                    ->label('Anuncio')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('dashboard.name')
                    ->label('Dashboard')
                    ->badge()
                    ->placeholder('Global'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'warning' => 'warning',
                        'celebration' => 'primary',
                        default => 'info',
                    }),

                TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Inmediato'),

                TextColumn::make('ends_at')
                    ->label('Final')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Sin límite'),

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
                        'info' => 'Información',
                        'success' => 'Éxito',
                        'warning' => 'Advertencia',
                        'celebration' => 'Celebración',
                    ]),

                Filter::make('currently_visible')
                    ->label('Visibles actualmente')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query
                                ->where('is_active', true)
                                ->where(function (Builder $query): void {
                                    $query
                                        ->whereNull('starts_at')
                                        ->orWhere('starts_at', '<=', now());
                                })
                                ->where(function (Builder $query): void {
                                    $query
                                        ->whereNull('ends_at')
                                        ->orWhere('ends_at', '>=', now());
                                })
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