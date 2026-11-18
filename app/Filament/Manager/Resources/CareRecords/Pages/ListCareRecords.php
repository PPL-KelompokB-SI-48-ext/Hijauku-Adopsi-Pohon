<?php

namespace App\Filament\Manager\Resources\CareRecords\Pages;

use App\Filament\Manager\Resources\CareRecords\CareRecordResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCareRecords extends ListRecords
{
    protected static string $resource = CareRecordResource::class;

    public function getTitle(): string
    {
        return 'Realisasi Perawatan';
    }

    public function getSubheading(): ?string
    {
        return 'Pencatatan bukti pelaksanaan perawatan fisik pohon oleh petugas yang membutuhkan verifikasi pengelola.';
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
}
