<?php

namespace App\Services\GrowthObservations;

use App\Models\GrowthObservation;
use App\Models\Tree;
use Illuminate\Support\Carbon;

class GrowthSummaryService
{
    public const UNMONITORED_THRESHOLD_DAYS = 30;

    public const NON_SDG_DISCLAIMER = 'Ringkasan dan grafik hanya merangkum data pengamatan fisik pohon yang telah terverifikasi di lapangan, dan tidak mengklaim angka atau capaian SDG.';

    /**
     * Menghasilkan ringkasan perkembangan pohon khusus dari pengamatan berstatus terverifikasi.
     * Pohon tanpa pengamatan terverifikasi dalam 30 hari terakhir ditandai belum terpantau.
     *
     * @return array<string, mixed>
     */
    public function summarize(?Carbon $referenceDate = null): array
    {
        $today = ($referenceDate ?? now())->copy()->startOfDay();
        $cutoffDate = $today->copy()->subDays(self::UNMONITORED_THRESHOLD_DAYS)->toDateString();

        $verifiedCount = GrowthObservation::query()->verified()->count();

        $trees = Tree::query()
            ->with(['latestVerifiedObservation'])
            ->get();

        $totalTrees = $trees->count();
        $monitoredCount = 0;
        $unmonitoredCount = 0;

        $conditionCounts = [
            GrowthObservation::CONDITION_HEALTHY => 0,
            GrowthObservation::CONDITION_FAIR => 0,
            GrowthObservation::CONDITION_NEEDS_RECOVERY => 0,
            'unmonitored' => 0,
        ];

        $verifiedHeights = [];
        $verifiedDiameters = [];

        foreach ($trees as $tree) {
            $latestVerified = $tree->latestVerifiedObservation;

            if (! $latestVerified instanceof GrowthObservation) {
                $unmonitoredCount++;
                $conditionCounts['unmonitored']++;

                continue;
            }

            if ($latestVerified->height_cm !== null) {
                $verifiedHeights[] = (float) $latestVerified->height_cm;
            }

            if ($latestVerified->diameter_cm !== null) {
                $verifiedDiameters[] = (float) $latestVerified->diameter_cm;
            }

            $observedDate = $latestVerified->observed_on?->toDateString();

            if ($observedDate === null || $observedDate < $cutoffDate) {
                $unmonitoredCount++;
                $conditionCounts['unmonitored']++;

                continue;
            }

            $monitoredCount++;

            if (array_key_exists($latestVerified->condition, $conditionCounts)) {
                $conditionCounts[$latestVerified->condition]++;
            }
        }

        return [
            'verified_observations_count' => $verifiedCount,
            'total_trees_count' => $totalTrees,
            'monitored_trees_count' => $monitoredCount,
            'unmonitored_trees_count' => $unmonitoredCount,
            'average_height_cm' => count($verifiedHeights) > 0
                ? round(array_sum($verifiedHeights) / count($verifiedHeights), 2)
                : null,
            'average_diameter_cm' => count($verifiedDiameters) > 0
                ? round(array_sum($verifiedDiameters) / count($verifiedDiameters), 2)
                : null,
            'condition_counts' => $conditionCounts,
            'threshold_days' => self::UNMONITORED_THRESHOLD_DAYS,
            'disclaimer' => self::NON_SDG_DISCLAIMER,
        ];
    }

    /**
     * Menghasilkan dataset grafik kondisi fisik pohon terverifikasi dan status belum terpantau (>30 hari).
     *
     * @return array<string, mixed>
     */
    public function chartData(?Carbon $referenceDate = null): array
    {
        $summary = $this->summarize($referenceDate);
        $counts = $summary['condition_counts'];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pohon (Pengamatan Terverifikasi)',
                    'data' => [
                        $counts[GrowthObservation::CONDITION_HEALTHY],
                        $counts[GrowthObservation::CONDITION_FAIR],
                        $counts[GrowthObservation::CONDITION_NEEDS_RECOVERY],
                        $counts['unmonitored'],
                    ],
                    'backgroundColor' => [
                        '#15803D',
                        '#0284C7',
                        '#D97706',
                        '#94A3B8',
                    ],
                ],
            ],
            'labels' => [
                'Baik / Sehat',
                'Cukup Baik',
                'Perlu Pemulihan',
                'Belum Terpantau (>30 Hari)',
            ],
        ];
    }
}
