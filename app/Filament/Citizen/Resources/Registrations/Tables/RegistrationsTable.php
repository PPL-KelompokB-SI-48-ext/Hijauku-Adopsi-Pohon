<?php

namespace App\Filament\Citizen\Resources\Registrations\Tables;

use App\Models\ActivityRegistration;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrationsTable
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
                TextColumn::make('activity.site.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity.schedule')
                    ->label('Jadwal Pelaksanaan')
                    ->state(fn (ActivityRegistration $record): string => $record->activity->starts_at->format('d M Y, H:i')
                        .' - '.$record->activity->ends_at->format('H:i').' WIB'),
                TextColumn::make('created_at')
                    ->label('Waktu Pendaftaran')
                    ->dateTime('d M Y, H:i:s \W\I\B')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status Pendaftaran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registered' => 'Terdaftar',
                        'attended' => 'Hadir',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'success',
                        'attended' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->emptyStateHeading('Belum ada pendaftaran kegiatan')
            ->emptyStateDescription('Anda belum mendaftar pada kegiatan lapangan manapun.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->defaultSort('created_at', 'desc');
    }
}
