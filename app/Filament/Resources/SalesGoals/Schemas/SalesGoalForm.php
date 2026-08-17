<?php

namespace App\Filament\Resources\SalesGoals\Schemas;

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesGoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Meta de ventas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),

                        Select::make('sales_team_id')
                            ->label('Equipo')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(
                                'Déjalo vacío si la meta aplica a varios equipos.'
                            ),

                        Select::make('goal_type')
                            ->label('Tipo de meta')
                            ->options([
                                GoalType::Revenue->value => 'Ingresos',
                                GoalType::SalesCount->value => 'Número de ventas',
                                GoalType::Calls->value => 'Llamadas',
                                GoalType::Appointments->value => 'Citas',
                                GoalType::Contracts->value => 'Contratos',
                                GoalType::Points->value => 'Puntos',
                            ])
                            ->default(GoalType::Revenue->value)
                            ->required(),

                        Select::make('period')
                            ->label('Periodo')
                            ->options([
                                GoalPeriod::Daily->value => 'Diaria',
                                GoalPeriod::Weekly->value => 'Semanal',
                                GoalPeriod::Monthly->value => 'Mensual',
                                GoalPeriod::Quarterly->value => 'Trimestral',
                                GoalPeriod::Yearly->value => 'Anual',
                                GoalPeriod::Custom->value => 'Personalizada',
                            ])
                            ->default(GoalPeriod::Monthly->value)
                            ->required(),

                        TextInput::make('target_value')
                            ->label('Valor de la meta')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('$'),

                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'USD' => 'USD - Dólar',
                                'MXN' => 'MXN - Peso mexicano',
                            ])
                            ->default('USD')
                            ->required(),

                        DateTimePicker::make('starts_at')
                            ->label('Inicio')
                            ->required()
                            ->seconds(false),

                        DateTimePicker::make('ends_at')
                            ->label('Final')
                            ->required()
                            ->seconds(false)
                            ->after('starts_at'),

                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),

                        Toggle::make('show_on_dashboard')
                            ->label('Mostrar en dashboard')
                            ->default(true),

                        Toggle::make('is_primary')
                            ->label('Meta principal')
                            ->helperText(
                                'Se utilizará como línea de llegada principal.'
                            )
                            ->default(false),
                    ]),
            ]);
    }
}