<?php

namespace App\Filament\Resources\LocalRankKeywords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

class LocalRankKeywordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make(
                    'keyword'
                )
                    ->label('Keyword')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make(
                    'location.name'
                )
                    ->label('Business')
                    ->sortable(),

                TextColumn::make(
                    'service'
                )
                    ->label('Service')
                    ->searchable(),

                TextColumn::make(
                    'default_grid_size'
                )
                    ->label('Grid')
                    ->formatStateUsing(
                        fn ($state) =>
                            "{$state} × {$state}"
                    ),

                TextColumn::make(
                    'default_radius_miles'
                )
                    ->label('Radius')
                    ->suffix(' mi'),

                IconColumn::make(
                    'is_active'
                )
                    ->label('Active')
                    ->boolean(),

                TextColumn::make(
                    'scans_count'
                )
                    ->label('Scans')
                    ->counts('scans'),

            ])
            ->defaultSort('keyword')
            ->filters([

                TernaryFilter::make(
                    'is_active'
                )
                    ->label('Active'),

            ])
            ->actions([

                EditAction::make(),

                DeleteAction::make(),

            ])
            ->bulkActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

            ]);
    }
}
