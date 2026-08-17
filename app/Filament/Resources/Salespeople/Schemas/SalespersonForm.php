<?php

namespace App\Filament\Resources\Salespeople\Schemas;

use App\Enums\SalespersonStatus;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalespersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información personal')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuario del sistema')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->unique(ignoreRecord: true),

                        Select::make('sales_team_id')
                            ->label('Equipo')
                            ->relationship(
                                name: 'team',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) =>
                                    $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload(),

                        TextInput::make('employee_number')
                            ->label('Número de empleado')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('display_name')
                            ->label('Nombre para mostrar')
                            ->maxLength(100),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(40),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                SalespersonStatus::Active->value => 'Activo',
                                SalespersonStatus::Inactive->value => 'Inactivo',
                                SalespersonStatus::Suspended->value => 'Suspendido',
                            ])
                            ->default(SalespersonStatus::Active->value)
                            ->required(),

                        TextInput::make('hire_date')
                            ->label('Fecha de contratación')
                            ->type('date'),
                    ]),

                Section::make('Avatar y dashboard')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Fotografía')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('salespeople/photos')
                            ->visibility('public'),

                        FileUpload::make('avatar_path')
                            ->label('Avatar animado')
                            ->image()
                            ->disk('public')
                            ->directory('salespeople/avatars')
                            ->visibility('public'),

                        ColorPicker::make('avatar_color')
                            ->label('Color del avatar'),

                        Select::make('avatar_animation')
                            ->label('Tipo de personaje')
                            ->options([
                                'runner' => 'Corredor',
                                'car' => 'Automóvil',
                                'rocket' => 'Cohete',
                                'horse' => 'Caballo',
                                'custom' => 'Personalizado',
                            ])
                            ->default('runner')
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('show_on_dashboard')
                            ->label('Mostrar en el dashboard')
                            ->default(true),
                    ]),
            ]);
    }
}