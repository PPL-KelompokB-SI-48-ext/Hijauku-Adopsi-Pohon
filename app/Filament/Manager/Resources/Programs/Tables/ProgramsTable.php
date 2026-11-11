<?php

namespace App\Filament\Manager\Resources\Programs\Tables;

use App\Actions\Programs\ActivateProgram;
use App\Actions\Programs\ArchiveProgram;
use App\Models\Program;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Program')
                    ->description(fn (Program $record): string => 'ID: #'.$record->getKey())
                    ->icon(Heroicon::OutlinedRectangleStack)
                    ->iconColor('success')
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
                        'completed' => 'Selesai',
                        'archived' => 'Diarsipkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'archived' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->options([
                        'draft' => 'Draf',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'archived' => 'Diarsipkan',
                    ])
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                Action::make('activate')
                    ->label('Aktifkan')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Program')
                    ->modalDescription('Pastikan nama, tujuan, dan periode program sudah benar. Program aktif dapat digunakan sebagai induk lokasi.')
                    ->modalSubmitActionLabel('Ya, Aktifkan')
                    ->successNotificationTitle('Program berhasil diaktifkan.')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan aktivasi')
                            ->helperText('Catatan ini disimpan di riwayat perubahan program.')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->visible(fn (Program $record): bool => $record->status === 'draft')
                    ->action(function (Program $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(ActivateProgram::class)->handle(
                            $actor,
                            $record,
                            $data['reason'],
                        );
                    }),
                Action::make('archive')
                    ->label('Arsipkan')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Arsipkan Program')
                    ->modalDescription('Program hanya diarsipkan. Data lokasi dan pohon yang sudah tercatat tetap disimpan.')
                    ->modalSubmitActionLabel('Ya, Arsipkan')
                    ->successNotificationTitle('Program berhasil diarsipkan.')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan pengarsipan')
                            ->helperText('Catatan ini disimpan di riwayat perubahan program.')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->visible(fn (Program $record): bool => $record->status === 'completed')
                    ->action(function (Program $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(ArchiveProgram::class)->handle(
                            $actor,
                            $record,
                            $data['reason'],
                        );
                    }),
            ])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Tambahkan program penghijauan untuk mulai mencatat tujuan dan periodenya.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->defaultSort('starts_on');
    }
}
