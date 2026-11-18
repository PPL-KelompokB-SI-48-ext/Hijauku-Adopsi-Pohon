<?php

namespace App\Filament\Manager\Resources\Sites\Pages;

use App\Filament\Manager\Resources\Sites\SiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Lokasi'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Daftar lokasi penanaman pohon di bawah naungan program penghijauan.';
    }

    public function getBreadcrumbs(): array
    {
        return ['Program & Lokasi', 'Lokasi Tanam'];
    }
}
