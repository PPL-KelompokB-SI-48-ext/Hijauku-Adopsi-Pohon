<?php

namespace App\Filament\Manager\Resources\CarePlans\Pages;

use App\Filament\Manager\Resources\CarePlans\CarePlanResource;
use App\Models\CarePlan;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCarePlans extends ListRecords
{
    protected static string $resource = CarePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Rencana'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Jadwal dan agenda teknis perawatan pohon berkala sebelum dieksekusi di lapangan.';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'overdue' => Tab::make('Terlambat')
                ->modifyQueryUsing(fn (Builder $query) => $query->overdue())
                ->badge(fn () => CarePlan::query()->overdue()->count() ?: null)
                ->badgeColor('danger'),
            'draft' => Tab::make('Draf')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'published' => Tab::make('Terbit')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
            'cancelled' => Tab::make('Dibatalkan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
