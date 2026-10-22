<?php

namespace App\Filament\Manager\Resources\Programs\Tables;

use App\Models\Program;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('objective')
                    ->label('Tujuan (Ringkas)')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('period')
                    ->label('Periode')
                    ->state(fn (Program $record): string => $record->starts_on->format('d/m/Y')
                        .' - '.($record->ends_on?->format('d/m/Y') ?? 'Tanpa batas')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'active' => 'Aktif',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        default => 'warning',
                    }),
            ])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Tambahkan program penghijauan untuk mulai mencatat tujuan dan periodenya.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->defaultSort('starts_on');
    }
}
