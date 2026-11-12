<?php

namespace App\Filament\FieldStaff\Resources\PlantingBatches\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlantingBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Kelompok')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plot.code')
                    ->label('Petak Tanam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('planted_on')
                    ->label('Tanggal Tanam')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('trees_count')
                    ->label('Pohon')
                    ->counts('trees')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(60)
                    ->wrap()
                    ->default('-'),

                TextColumn::make('created_at')
                    ->label('Dicatat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Belum ada kelompok penanaman')
            ->emptyStateDescription('Catat pelaksanaan penanaman baru pada petak tanam.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->defaultSort('planted_on', 'desc');
    }
}
