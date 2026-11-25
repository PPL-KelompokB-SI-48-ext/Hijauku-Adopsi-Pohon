<?php

namespace App\Filament\Manager\Resources\GrowthObservations\Pages;

use App\Filament\Manager\Resources\GrowthObservations\GrowthObservationResource;
use App\Filament\Manager\Widgets\GrowthConditionChartWidget;
use App\Filament\Manager\Widgets\GrowthSummaryStatsWidget;
use App\Filament\Manager\Widgets\UnmonitoredTreesWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListGrowthObservations extends ListRecords
{
    protected static string $resource = GrowthObservationResource::class;

    public function getTitle(): string
    {
        return 'Pengamatan Pertumbuhan';
    }

    public function getSubheading(): ?string
    {
        return 'Pencatatan metrik fisik tinggi, diameter batang, serta kondisi pohon secara berkala (ringkasan non-SDG).';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Segarkan')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->action(fn () => null),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GrowthSummaryStatsWidget::class,
            GrowthConditionChartWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            UnmonitoredTreesWidget::class,
        ];
    }
}
