<?php

namespace App\Filament\Manager\Resources\Sites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components())
            ->columns(2);
    }

    /**
     * @return array<int, Component>
     */
    public static function components(bool $includeReason = false): array
    {
        $components = [
            Select::make('program_id')
                ->label('Program induk')
                ->relationship('program', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->validationMessages([
                    'required' => 'Program induk lokasi wajib dipilih.',
                    'exists' => 'Program induk yang dipilih tidak tersedia.',
                ]),
            TextInput::make('name')
                ->label('Nama lokasi')
                ->required()
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Nama lokasi wajib diisi.',
                ]),
            Textarea::make('address')
                ->label('Alamat lengkap')
                ->required()
                ->maxLength(5000)
                ->rows(3)
                ->columnSpanFull()
                ->validationMessages([
                    'required' => 'Alamat lokasi wajib diisi.',
                ]),
            TextInput::make('latitude')
                ->label('Latitude')
                ->numeric()
                ->minValue(-90)
                ->maxValue(90)
                ->step('0.0000001')
                ->placeholder('-6.8900000')
                ->helperText('Opsional. Nilai berada dalam rentang -90 sampai 90.'),
            TextInput::make('longitude')
                ->label('Longitude')
                ->numeric()
                ->minValue(-180)
                ->maxValue(180)
                ->step('0.0000001')
                ->placeholder('107.6100000')
                ->helperText('Opsional. Nilai berada dalam rentang -180 sampai 180.'),
        ];

        if ($includeReason) {
            $components[] = Textarea::make('reason')
                ->label('Alasan perubahan')
                ->required()
                ->minLength(3)
                ->maxLength(1000)
                ->rows(3)
                ->columnSpanFull()
                ->helperText('Alasan dan nilai sebelum/sesudah akan disimpan di riwayat perubahan.')
                ->validationMessages([
                    'required' => 'Alasan perubahan wajib diisi.',
                    'min' => 'Alasan perubahan minimal 3 karakter.',
                ]);
        }

        return $components;
    }
}
