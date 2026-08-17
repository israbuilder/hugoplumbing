<?php

namespace App\Filament\Resources\DashboardSlides\Schemas;

use App\Enums\DashboardSlideType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DashboardSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slide')
                    ->columns(2)
                    ->schema([
                        Select::make('dashboard_id')
                            ->label('Dashboard')
                            ->relationship('dashboard', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('type')
                            ->label('Tipo de pantalla')
                            ->options([
                                DashboardSlideType::Race->value => 'Carrera',
                                DashboardSlideType::Leaderboard->value => 'Ranking',
                                DashboardSlideType::DailySales->value => 'Ventas del día',
                                DashboardSlideType::GoalProgress->value => 'Progreso de meta',
                                DashboardSlideType::TopPerformer->value => 'Mejor vendedor',
                                DashboardSlideType::TeamComparison->value => 'Comparación de equipos',
                                DashboardSlideType::Announcement->value => 'Anuncio',
                                DashboardSlideType::Custom->value => 'Personalizado',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('name')
                            ->label('Nombre interno')
                            ->required(),

                        TextInput::make('title')
                            ->label('Título'),

                        TextInput::make('subtitle')
                            ->label('Subtítulo')
                            ->columnSpanFull(),

                        Select::make('sales_goal_id')
                            ->label('Meta')
                            ->relationship('goal', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('sales_team_id')
                            ->label('Equipo')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('duration_seconds')
                            ->label('Duración')
                            ->numeric()
                            ->suffix('segundos')
                            ->default(15)
                            ->minValue(3)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }
}