<?php

namespace App\Filament\Manager\Resources\Activities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => 'Judul kegiatan wajib diisi.',
                    ]),
                Select::make('site_id')
                    ->label('Lokasi kegiatan')
                    ->relationship('site', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Kegiatan wajib terikat pada satu lokasi.',
                    ]),
                Select::make('kind')
                    ->label('Jenis kegiatan')
                    ->options([
                        'care' => 'Perawatan',
                        'planting' => 'Penanaman',
                        'maintenance' => 'Pemeliharaan',
                        'socialization' => 'Sosialisasi',
                    ])
                    ->required()
                    ->default('care')
                    ->native(false)
                    ->validationMessages([
                        'required' => 'Jenis kegiatan wajib dipilih.',
                    ]),
                DateTimePicker::make('starts_at')
                    ->label('Waktu mulai')
                    ->required()
                    ->native(false)
                    ->validationMessages([
                        'required' => 'Waktu mulai kegiatan wajib diisi.',
                    ]),
                DateTimePicker::make('ends_at')
                    ->label('Waktu selesai')
                    ->required()
                    ->after('starts_at')
                    ->native(false)
                    ->validationMessages([
                        'required' => 'Waktu selesai kegiatan wajib diisi.',
                        'after' => 'Waktu akhir harus setelah waktu awal.',
                    ]),
                TextInput::make('quota')
                    ->label('Kuota peserta')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(65535)
                    ->placeholder('Opsional (kosongkan jika tanpa batas kuota)')
                    ->helperText('Isi bilangan bulat positif jika kegiatan membatasi kuota peserta.')
                    ->validationMessages([
                        'numeric' => 'Kuota berupa bilangan positif bila diisi.',
                        'min' => 'Kuota berupa bilangan positif bila diisi.',
                    ]),
            ])
            ->columns(2);
    }
}

