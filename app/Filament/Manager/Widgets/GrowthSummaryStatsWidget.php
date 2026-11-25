<?php

namespace App\Filament\Manager\Widgets;

use App\Models\User;
use App\Services\GrowthObservations\GrowthSummaryService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GrowthSummaryStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ringkasan Perkembangan Pohon (Terverifikasi)';

    protected ?string $description = 'Hanya pengamatan berstatus terverifikasi yang masuk ringkasan. Pengamatan diajukan atau dikembalikan tidak dihitung.';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isManager();
    }

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $summary = app(GrowthSummaryService::class)->summarize();

        $avgHeight = $summary['average_height_cm'];
        $avgDiameter = $summary['average_diameter_cm'];
        $avgText = ($avgHeight !== null && $avgDiameter !== null)
            ? number_format((float) $avgHeight, 1, ',', '.').' cm / '.number_format((float) $avgDiameter, 1, ',', '.').' cm'
            : 'Belum ada data';

        return [
            Stat::make('Pengamatan Terverifikasi', (string) $summary['verified_observations_count'])
                ->description('Total catatan pertumbuhan terverifikasi')
                ->color('success'),

            Stat::make('Rata-rata Tinggi / Diameter', $avgText)
                ->description('Dari pengamatan terverifikasi terbaru per pohon')
                ->color('info'),

            Stat::make('Belum Terpantau (>30 Hari)', (string) $summary['unmonitored_trees_count'])
                ->description('Pohon tanpa pengamatan terverifikasi >30 hari')
                ->color($summary['unmonitored_trees_count'] > 0 ? 'warning' : 'success'),
        ];
    }
}
