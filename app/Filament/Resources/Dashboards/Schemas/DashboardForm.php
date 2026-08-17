<?php

namespace App\Filament\Resources\Dashboards\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DashboardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configuración del dashboard')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn (?string $state, Set $set) =>
                                    $set('slug', Str::slug($state ?? ''))
                            ),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('access_token')
                            ->label('Token de acceso')
                            ->default(fn () => Str::random(64))
                            ->readOnly()
                            ->dehydrated()
                            ->copyable(),

                        Select::make('timezone')
                            ->label('Zona horaria')
                            ->options([
                                'America/Chicago' => 'Houston / Chicago',
                                'America/Los_Angeles' => 'Los Ángeles',
                                'America/New_York' => 'Nueva York',
                                'America/Mexico_City' => 'Ciudad de México',
                            ])
                            ->default('America/Chicago')
                            ->searchable()
                            ->required(),

                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'USD' => 'USD',
                                'MXN' => 'MXN',
                            ])
                            ->default('USD')
                            ->required(),

                        Select::make('theme')
                            ->label('Tema')
                            ->options([
                                'dark' => 'Oscuro',
                                'light' => 'Claro',
                                'company' => 'Corporativo',
                            ])
                            ->default('dark')
                            ->required(),

                        TextInput::make('default_slide_duration')
                            ->label('Duración predeterminada')
                            ->numeric()
                            ->suffix('segundos')
                            ->default(15)
                            ->minValue(3)
                            ->required(),

                        TextInput::make('refresh_interval')
                            ->label('Intervalo de actualización')
                            ->numeric()
                            ->suffix('segundos')
                            ->default(10)
                            ->minValue(2)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }
}