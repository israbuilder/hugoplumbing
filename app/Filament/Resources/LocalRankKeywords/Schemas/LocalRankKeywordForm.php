<?php

namespace App\Filament\Resources\LocalRankKeywords\Schemas;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\LocalRankLocation;
use Filament\Schemas\Schema;

class LocalRankKeywordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Select::make(
                    'local_rank_location_id'
                )
                    ->label('Business')
                    ->options(
                        LocalRankLocation::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make(
                    'keyword'
                )
                    ->label('Keyword')
                    ->placeholder(
                        'water heater repair'
                    )
                    ->required()
                    ->maxLength(255),

                TextInput::make(
                    'service'
                )
                    ->label('Service')
                    ->placeholder(
                        'Water Heater'
                    )
                    ->maxLength(255),

                Forms\Components\Select::make(
                    'default_grid_size'
                )
                    ->label('Default Grid')
                    ->options([
                        3 => '3 × 3',
                        5 => '5 × 5',
                        7 => '7 × 7',
                        9 => '9 × 9',
                        11 => '11 × 11',
                        13 => '13 × 13',
                    ])
                    ->default(5)
                    ->required(),

                TextInput::make(
                    'default_radius_miles'
                )
                    ->label('Default Radius')
                    ->numeric()
                    ->suffix('miles')
                    ->default(5)
                    ->minValue(0.5)
                    ->step(0.5)
                    ->required(),

                Select::make(
                    'zoom'
                )
                    ->label('Google Maps Zoom')
                    ->options([
                        10 => '10',
                        11 => '11',
                        12 => '12',
                        13 => '13',
                        14 => '14',
                        15 => '15',
                        16 => '16',
                        17 => '17',
                    ])
                    ->default(15)
                    ->required(),

                Toggle::make(
                    'is_active'
                )
                    ->label('Active')
                    ->default(true),

            ])
            ->columns(2);
    }
}
