<?php

namespace App\Filament\Manager\Widgets;

use App\Models\User;
use App\Services\GrowthObservations\GrowthSummaryService;
use Filament\Widgets\ChartWidget;

class GrowthConditionChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Grafik Kondisi Fisik Pohon (Non-SDG)';

    protected ?string $description = GrowthSummaryService::NON_SDG_DISCLAIMER;

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isManager();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        return app(GrowthSummaryService::class)->chartData();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
