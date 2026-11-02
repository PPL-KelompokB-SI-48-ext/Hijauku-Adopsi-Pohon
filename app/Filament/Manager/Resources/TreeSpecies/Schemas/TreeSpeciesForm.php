<?php

namespace App\Filament\Manager\Resources\TreeSpecies\Schemas;

use App\Models\TreeSpecies;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TreeSpeciesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama jenis pohon')
                    ->required()
                    ->maxLength(255)
                    ->unique(TreeSpecies::class, 'name', ignoreRecord: true)
                    ->placeholder('Contoh: Trembesi (Samanea saman)')
                    ->validationMessages([
                        'required' => 'Nama jenis pohon wajib diisi.',
                        'unique' => 'Nama jenis pohon sudah terdaftar.',
                    ])
                    ->columnSpanFull(),
                Textarea::make('care_notes')
                    ->label('Catatan perawatan')
                    ->rows(4)
                    ->placeholder('Contoh: Kebutuhan air sedang, cocok untuk peneduh dan tanah terbuka.')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Status aktif')
                    ->default(true)
                    ->helperText('Hanya jenis pohon aktif yang dapat dipilih pada inventaris pohon baru.')
                    ->columnSpanFull(),
            ]);
    }
}
