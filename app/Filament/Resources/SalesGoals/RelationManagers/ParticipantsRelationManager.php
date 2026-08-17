<?php

namespace App\Filament\Resources\SalesGoals\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Participantes';

    protected static ?string $modelLabel = 'participante';

    protected static ?string $pluralModelLabel = 'participantes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('salesperson_id')
                    ->label('Vendedor')
                    ->relationship(
                        name: 'salesperson',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder =>
                            $query
                                ->where('status', 'active')
                                ->where('show_on_dashboard', true)
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'sales_goal_participants',
                        column: 'salesperson_id',
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule) =>
                            $rule->where(
                                'sales_goal_id',
                                $this->getOwnerRecord()->getKey()
                            )
                    ),

                TextInput::make('target_value')
                    ->label('Meta individual')
                    ->numeric()
                    ->minValue(0)
                    ->helperText(
                        'Vacío utiliza el objetivo general de la meta.'
                    ),

                TextInput::make('starting_value')
                    ->label('Valor inicial')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('salesperson.name')
            ->columns([
                ImageColumn::make('salesperson.avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('salesperson.name')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salesperson.team.name')
                    ->label('Equipo')
                    ->badge()
                    ->placeholder('Sin equipo'),

                TextColumn::make('target_value')
                    ->label('Meta individual')
                    ->money(
                        fn (): string =>
                            $this->getOwnerRecord()->currency
                    )
                    ->placeholder('Meta general'),

                TextColumn::make('starting_value')
                    ->label('Valor inicial')
                    ->numeric(decimalPlaces: 2),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}