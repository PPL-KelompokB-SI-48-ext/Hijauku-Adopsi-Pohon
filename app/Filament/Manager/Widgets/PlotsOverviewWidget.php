<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Plot;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlotsOverviewWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = ['default' => 1, 'md' => 2, 'xl' => 4];

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isManager();
    }

    protected function getStats(): array
    {
        $plots = Plot::query()
            ->withCount('trees')
            ->get(['id', 'capacity', 'status']);

        $totalCapacity = (int) $plots->sum('capacity');
        $occupied = (int) $plots->sum('trees_count');
        $ready = $plots->filter(fn (Plot $plot): bool => $plot->status === 'open' && $plot->trees_count < $plot->capacity)->count();
        $drafts = $plots->whereIn('status', ['draft', 'revision'])->count();
        $percentage = $totalCapacity > 0 ? (int) round(($occupied / $totalCapacity) * 100) : 0;

        return [
            Stat::make('Total Kapasitas', number_format($totalCapacity))
                ->description('Pohon')
                ->icon(Heroicon::OutlinedCalculator)
                ->extraAttributes(['class' => 'plot-stat plot-stat-capacity'])
                ->color('primary'),
            Stat::make('Terisi Saat Ini', number_format($occupied))
                ->description("Pohon ({$percentage}%)")
                ->icon(Heroicon::OutlinedChartBar)
                ->extraAttributes(['class' => 'plot-stat plot-stat-occupied'])
                ->color('success'),
            Stat::make('Petak Siap Pakai', number_format($ready))
                ->description('Zona')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->extraAttributes(['class' => 'plot-stat plot-stat-ready'])
                ->color('info'),
            Stat::make('Draf / Revisi', number_format($drafts))
                ->description('Petak')
                ->icon(Heroicon::OutlinedDocumentText)
                ->extraAttributes(['class' => 'plot-stat plot-stat-drafts'])
                ->color('warning'),
        ];
    }
}
