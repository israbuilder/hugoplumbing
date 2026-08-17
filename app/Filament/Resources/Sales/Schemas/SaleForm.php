<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Enums\SaleStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la venta')
                    ->columns(2)
                    ->schema([
                        Select::make('salesperson_id')
                            ->label('Vendedor')
                            ->relationship('salesperson', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('sales_goal_id')
                            ->label('Meta')
                            ->relationship('goal', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('reference_number')
                            ->label('Número de referencia')
                            ->maxLength(100),

                        TextInput::make('customer_name')
                            ->label('Cliente')
                            ->maxLength(150),

                        TextInput::make('amount')
                            ->label('Importe')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0),

                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'USD' => 'USD',
                                'MXN' => 'MXN',
                            ])
                            ->default('USD')
                            ->required(),

                        TextInput::make('points')
                            ->label('Puntos')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                SaleStatus::Pending->value => 'Pendiente',
                                SaleStatus::Approved->value => 'Aprobada',
                                SaleStatus::Cancelled->value => 'Cancelada',
                                SaleStatus::Refunded->value => 'Reembolsada',
                            ])
                            ->default(SaleStatus::Approved->value)
                            ->required(),

                        DateTimePicker::make('sold_at')
                            ->label('Fecha de venta')
                            ->default(now())
                            ->required()
                            ->seconds(false),

                        Select::make('source')
                            ->label('Origen')
                            ->options([
                                'manual' => 'Manual',
                                'crm' => 'CRM',
                                'webhook' => 'Webhook',
                                'csv' => 'CSV',
                                'hubspot' => 'HubSpot',
                                'salesforce' => 'Salesforce',
                            ])
                            ->default('manual')
                            ->required(),

                        TextInput::make('external_id')
                            ->label('ID externo')
                            ->maxLength(150),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),

                        Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                    ]),

                Section::make('Actividad')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextInput::make('calls_count')
                            ->label('Llamadas')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('appointments_count')
                            ->label('Citas')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('contracts_count')
                            ->label('Contratos')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ]);
    }
}