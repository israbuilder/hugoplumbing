<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anuncio')
                    ->columns(2)
                    ->schema([
                        Select::make('dashboard_id')
                            ->label('Dashboard')
                            ->relationship('dashboard', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(
                                'Vacío mostrará el anuncio globalmente.'
                            ),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'info' => 'Información',
                                'success' => 'Éxito',
                                'warning' => 'Advertencia',
                                'celebration' => 'Celebración',
                            ])
                            ->default('info')
                            ->required(),

                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(180)
                            ->columnSpanFull(),

                        Textarea::make('message')
                            ->label('Mensaje')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('image_path')
                            ->label('Imagen')
                            ->image()
                            ->disk('public')
                            ->directory('announcements')
                            ->visibility('public'),

                        TextInput::make('video_url')
                            ->label('URL del video')
                            ->url(),

                        DateTimePicker::make('starts_at')
                            ->label('Mostrar desde')
                            ->seconds(false),

                        DateTimePicker::make('ends_at')
                            ->label('Mostrar hasta')
                            ->seconds(false)
                            ->after('starts_at'),

                        TextInput::make('duration_seconds')
                            ->label('Duración')
                            ->numeric()
                            ->suffix('segundos')
                            ->default(10)
                            ->minValue(3),

                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),

                        Toggle::make('show_once')
                            ->label('Mostrar una sola vez')
                            ->default(false),

                        Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }
}