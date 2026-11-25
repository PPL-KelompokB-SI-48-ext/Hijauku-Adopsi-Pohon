<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Tree;
use App\Models\User;
use App\Services\GrowthObservations\GrowthSummaryService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UnmonitoredTreesWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isManager();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tree::query()
                    ->unmonitored(GrowthSummaryService::UNMONITORED_THRESHOLD_DAYS)
                    ->with(['species', 'plot.site', 'latestVerifiedObservation'])
            )
            ->heading('Pohon Belum Terpantau (>30 Hari)')
            ->description('Daftar pohon yang belum memiliki pengamatan pertumbuhan terverifikasi dalam 30 hari terakhir.')
            ->columns([
                TextColumn::make('code')
                    ->label('Pohon')
                    ->description(fn (Tree $record): ?string => ($record->species?->name ?? '-')
                        .' - Petak '.($record->plot?->code ?? '-'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plot.site.name')
                    ->label('Lokasi')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('latestVerifiedObservation.observed_on')
                    ->label('Pengamatan Terverifikasi Terakhir')
                    ->date('d M Y')
                    ->placeholder('Belum pernah diamati')
                    ->sortable(),

                TextColumn::make('monitoring_status')
                    ->label('Status Pemantauan')
                    ->state(fn (): string => 'Belum Terpantau')
                    ->badge()
                    ->color('warning')
                    ->description(fn (Tree $record): string => $record->latestVerifiedObservation
                        ? 'Lebih dari 30 hari sejak pengamatan terverifikasi terakhir'
                        : 'Belum ada pengamatan terverifikasi'),
            ])
            ->emptyStateHeading('Semua pohon terpantau')
            ->emptyStateDescription('Seluruh pohon memiliki pengamatan terverifikasi dalam 30 hari terakhir.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultPaginationPageOption(10)
            ->defaultSort('code', 'asc');
    }
}
