<?php

namespace App\Filament\Manager\Resources\FieldAssignments\Tables;

use App\Models\FieldAssignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FieldAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tree.species.name')
                    ->label('Spesies Pohon')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('activity.site.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assignee.name')
                    ->label('Petugas Lapangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label('Tenggat')
                    ->dateTime('d M Y, H:i')
                    ->formatStateUsing(fn (FieldAssignment $record): string => $record->due_at
                        ? $record->due_at->format('d M Y, H:i').' WIB'
                        : '-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'assigned' => 'Ditugaskan',
                        'confirmed' => 'Dikonfirmasi',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'assigned' => 'warning',
                        'confirmed' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Penugasan')
                    ->options([
                        'assigned' => 'Ditugaskan',
                        'confirmed' => 'Dikonfirmasi',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ]),

                SelectFilter::make('activity_id')
                    ->label('Kegiatan')
                    ->relationship('activity', 'title'),

                SelectFilter::make('assignee_id')
                    ->label('Petugas')
                    ->relationship('assignee', 'name'),
            ])
            ->emptyStateHeading('Belum ada penugasan lapangan')
            ->emptyStateDescription('Tetapkan petugas lapangan pada pohon dan kegiatan yang sesuai lokasi.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->defaultSort('created_at', 'desc');
    }
}
