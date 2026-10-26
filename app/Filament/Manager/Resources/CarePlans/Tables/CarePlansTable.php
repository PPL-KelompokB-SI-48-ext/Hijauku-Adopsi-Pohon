<?php

namespace App\Filament\Manager\Resources\CarePlans\Tables;

use App\Models\CarePlan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CarePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(fn (CarePlan $record): ?string => $record->tree ? ($record->tree->species?->name . ' - Petak ' . $record->tree->plot?->code) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('planned_on')
                    ->label('Tanggal Rencana')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Tindakan (Ringkas)')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('assignment_info')
                    ->label('Penugasan (Opsional)')
                    ->state(function (CarePlan $record): string {
                        if (! $record->assignment) {
                            return '-(Belum ditugaskan)';
                        }
                        $code = 'TGS-' . str_pad((string) $record->assignment->id, 4, '0', STR_PAD_LEFT);
                        $assignee = $record->assignment->assignee?->name;
                        return $assignee ? "{$code} ({$assignee})" : $code;
                    })
                    ->badge()
                    ->color(fn (CarePlan $record): string => $record->assignment ? 'info' : 'gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'published', 'terbit' => 'Terbit',
                        'completed', 'selesai' => 'Selesai',
                        'cancelled', 'dibatalkan' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published', 'terbit' => 'info',
                        'completed', 'selesai' => 'success',
                        'cancelled', 'dibatalkan' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Semua Status')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Terbit',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->emptyStateHeading('Belum ada rencana perawatan')
            ->emptyStateDescription('Susun rencana perawatan pohon berkala sebelum dieksekusi di lapangan.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('planned_on', 'desc');
    }
}
