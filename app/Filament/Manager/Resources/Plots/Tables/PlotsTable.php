<?php

namespace App\Filament\Manager\Resources\Plots\Tables;

use App\Filament\Manager\Resources\Plots\Pages\ListPlots;
use App\Filament\Manager\Resources\Plots\Schemas\PlotForm;
use App\Models\Plot;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Petak')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site.name')
                    ->label('Lokasi')
                    ->description(fn (Plot $record): ?string => $record->site?->program?->name)
                    ->icon(Heroicon::OutlinedMapPin)
                    ->iconColor('success')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->formatStateUsing(fn (int|string $state): string => number_format((int) $state).' Pohon')
                    ->sortable(),
                ViewColumn::make('occupancy')
                    ->label('Jumlah Pohon (Turunan)')
                    ->view('filament.manager.resources.plots.tables.occupancy', fn (Plot $record): array => [
                        'record' => $record,
                        'count' => (int) $record->trees_count,
                        'percentage' => $record->capacity > 0
                            ? (int) round(($record->trees_count / $record->capacity) * 100)
                            : 0,
                        'warning' => $record->capacity > 0
                            && $record->trees_count < $record->capacity
                            && ($record->trees_count * 100) >= ($record->capacity * 90),
                    ])
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('trees_count', $direction)),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Plot $record): string => self::statusLabel($record))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Penuh' => 'info',
                        'Draf', 'Revisi' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->label('Lokasi')
                    ->placeholder('Semua Lokasi')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->searchPlaceholder('Cari kode petak...')
            ->headerActions([
                Action::make('reset')
                    ->label('Atur Ulang')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->action(fn (ListPlots $livewire) => $livewire->removeTableFilters()),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->authorize('view')
                    ->schema(PlotForm::components()),
            ])
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([20, 50])
            ->emptyStateHeading('Belum ada petak tanam')
            ->emptyStateDescription('Tambahkan petak dan kapasitasnya pada lokasi yang sudah terdaftar.')
            ->emptyStateIcon('heroicon-o-map')
            ->defaultSort('code');
    }

    private static function statusLabel(Plot $plot): string
    {
        if (in_array($plot->status, ['draft', 'revision'], true)) {
            return $plot->status === 'draft' ? 'Draf' : 'Revisi';
        }

        if ($plot->status !== 'open') {
            return 'Ditutup';
        }

        return $plot->trees_count >= $plot->capacity ? 'Penuh' : 'Tersedia';
    }
}
