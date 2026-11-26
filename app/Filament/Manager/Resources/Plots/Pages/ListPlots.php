<?php

namespace App\Filament\Manager\Resources\Plots\Pages;

use App\Filament\Manager\Resources\Plots\PlotResource;
use App\Filament\Manager\Widgets\PlotsOverviewWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlots extends ListRecords
{
    protected static string $resource = PlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Petak'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PlotsOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return ['default' => 1, 'md' => 2, 'xl' => 4];
    }

    public function getSubheading(): ?string
    {
        return 'Zonasi petak tanam, pemantauan kapasitas, dan alokasi pohon penanaman.';
    }

    public function getBreadcrumbs(): array
    {
        return ['Program & Lokasi', 'Petak Tanam'];
    }
}
