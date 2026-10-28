<?php

namespace App\Filament\Manager\Resources\Activities\Tables;

use App\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('kind')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'care' => 'Perawatan',
                        'planting' => 'Penanaman',
                        'maintenance' => 'Pemeliharaan',
                        'socialization' => 'Sosialisasi',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'care' => 'info',
                        'planting' => 'success',
                        'maintenance' => 'warning',
                        'socialization' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('site.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schedule')
                    ->label('Jadwal Pelaksanaan')
                    ->state(fn (Activity $record): string => $record->starts_at->format('d M Y, H:i')
                        .' - '.$record->ends_at->format('H:i').' WIB'),
                TextColumn::make('quota')
                    ->label('Kuota Peserta')
                    ->state(fn (Activity $record): string => $record->quota ? "{$record->quota} Peserta" : 'Tanpa kuota'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'open' => 'Terbuka',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'open' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'info',
                    }),
            ])
            ->emptyStateHeading('Belum ada kegiatan lapangan')
            ->emptyStateDescription('Tambahkan kegiatan lapangan baru untuk mulai menjadwalkan aksi di lokasi.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('starts_at');
    }
}

