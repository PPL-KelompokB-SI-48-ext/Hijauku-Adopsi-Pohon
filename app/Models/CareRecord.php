<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CareRecord extends Model
{
    protected $fillable = ['tree_id', 'plan_id', 'performed_by', 'performed_on', 'action', 'photo_path', 'status', 'verified_by', 'verified_at', 'review_note'];

    protected function casts(): array
    {
        return ['performed_on' => 'date', 'verified_at' => 'datetime'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'plan_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
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
     * Ringkasan kronologis jejak audit untuk keperluan verifikasi dan audit sistem.
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
                        'care_record.created' => 'Laporan Diajukan',
                        'care_record.verified' => 'Verifikasi Diterima',
                        'care_record.returned' => 'Dikembalikan untuk Perbaikan',
                        'care_record.corrected' => 'Koreksi Data Tercatat',
                        'care_record.resubmitted' => 'Diajukan Ulang setelah Perbaikan',
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
     * Scope: only records belonging to a specific field staff actor.
     */
    public function scopeForPerformer(Builder $query, User|int $user): Builder
    {
        $id = $user instanceof User ? $user->getKey() : $user;

        return $query->where('performed_by', $id);
    }
}
