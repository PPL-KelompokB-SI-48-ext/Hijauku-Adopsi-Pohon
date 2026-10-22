<?php

namespace App\Filament\Manager\Resources\Programs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama program')
                    ->required()
                    ->maxLength(255),
                Textarea::make('objective')
                    ->label('Tujuan program')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                DatePicker::make('starts_on')
                    ->label('Tanggal mulai')
                    ->required()
                    ->native(false),
                DatePicker::make('ends_on')
                    ->label('Tanggal akhir')
                    ->required()
                    ->after('starts_on')
                    ->native(false)
                    ->validationMessages([
                        'after' => 'Tanggal akhir harus setelah tanggal mulai.',
                    ]),
            ])
            ->columns(2);
    }
}
