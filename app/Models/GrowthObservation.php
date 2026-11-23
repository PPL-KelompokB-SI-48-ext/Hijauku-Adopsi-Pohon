<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GrowthObservation extends Model
{
    public const CONDITION_HEALTHY = 'healthy';

    public const CONDITION_FAIR = 'fair';

    public const CONDITION_NEEDS_RECOVERY = 'needs_recovery';

    protected $fillable = ['tree_id', 'observed_by', 'observed_on', 'height_cm', 'diameter_cm', 'condition', 'photo_path', 'status', 'verified_by', 'verified_at', 'review_note'];

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'verified_at' => 'datetime',
            'height_cm' => 'decimal:2',
            'diameter_cm' => 'decimal:2',
        ];
    }

    /**
     * Pilihan kondisi fisik pohon yang disepakati untuk pengamatan pertumbuhan.
     *
     * @return array<string, string>
     */
    public static function conditionOptions(): array
    {
        return [
            self::CONDITION_HEALTHY => 'Baik / Sehat',
            self::CONDITION_FAIR => 'Cukup Baik',
            self::CONDITION_NEEDS_RECOVERY => 'Perlu Pemulihan',
        ];
    }

    /**
     * Mengembalikan label kondisi pohon dalam Bahasa Indonesia.
     */
    public static function conditionLabel(string $condition): string
    {
        return self::conditionOptions()[$condition] ?? ucfirst(str_replace('_', ' ', $condition));
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observed_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /**
     * Riwayat audit verifikasi dan perubahan keputusan secara kronologis.
     */
    public function auditTrail(): MorphMany
    {
        return $this->activityLogs()->with('actor')->orderBy('created_at', 'asc');
    }

    /**
     * Ringkasan kronologis jejak audit pengamatan pertumbuhan.
     *
     * @return list<array<string, mixed>>
     */
    public function getAuditSummary(): array
    {
        return $this->activityLogs()
            ->with('actor')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (ActivityLog $log): array {
                return [
                    'time' => $log->created_at?->format('d M Y H:i:s'),
                    'actor' => $log->actor?->name ?? 'Sistem',
                    'role' => $log->actor?->role instanceof \BackedEnum ? $log->actor->role->value : (string) ($log->actor?->role ?? '-'),
                    'action' => match ($log->action) {
                        'growth_observation.created' => 'Pengamatan Diajukan',
                        'growth_observation.verified' => 'Verifikasi Diterima',
                        'growth_observation.returned' => 'Dikembalikan untuk Perbaikan',
                        'growth_observation.corrected' => 'Koreksi Data Tercatat',
                        'growth_observation.resubmitted' => 'Diajukan Ulang setelah Perbaikan',
                        default => $log->action,
                    },
                    'from_status' => $log->from_status,
                    'to_status' => $log->to_status,
                    'note' => $log->metadata['decision_reason'] ?? $log->metadata['reason'] ?? $log->metadata['note'] ?? null,
                    'changes' => $log->metadata['changes'] ?? null,
                ];
            })
            ->all();
    }

    /**
     * Batasi query ke pengamatan milik petugas tertentu.
     */
    public function scopeForObserver(Builder $query, User|int $user): Builder
    {
        $id = $user instanceof User ? $user->getKey() : $user;

        return $query->where('observed_by', $id);
    }
}
