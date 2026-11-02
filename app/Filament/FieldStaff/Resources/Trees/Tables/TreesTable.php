<?php

namespace App\Filament\FieldStaff\Resources\Trees\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Pohon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('species.name')
                    ->label('Jenis Pohon')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('plot.code')
                    ->label('Petak')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('initial_condition')
                    ->label('Kondisi Awal')
                    ->default('-'),

                TextColumn::make('planted_on')
                    ->label('Tanggal Tanam')
                    ->date('d/m/Y')
                    ->sortable()
                    ->default('-'),
            ])
            ->emptyStateHeading('Belum ada pohon')
            ->emptyStateDescription('Mulai mencatat pohon baru untuk inventaris.')
            ->defaultSort('code');
    }
}
