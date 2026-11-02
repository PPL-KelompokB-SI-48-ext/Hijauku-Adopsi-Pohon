<?php

namespace App\Filament\Manager\Resources\TreeSpecies\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreeSpeciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Jenis Pohon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('care_notes')
                    ->label('Catatan Perawatan')
                    ->limit(80)
                    ->wrap()
                    ->default('-'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('trees_count')
                    ->label('Pohon Terdaftar')
                    ->counts('trees')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Didaftarkan Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Belum ada jenis pohon')
            ->emptyStateDescription('Tambahkan jenis pohon baru untuk mulai mencatat acuan katalog pohon.')
            ->emptyStateIcon('heroicon-o-tag')
            ->defaultSort('name');
    }
}
