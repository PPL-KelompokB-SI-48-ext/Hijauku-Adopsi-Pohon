<?php

namespace App\Filament\Manager\Resources\CarePlans\Tables;

use App\Actions\CarePlans\CancelCarePlan;
use App\Actions\CarePlans\CorrectCarePlan;
use App\Actions\CarePlans\UpdateCarePlanPriority;
use App\Models\CarePlan;
use App\Models\Site;
use App\Models\Tree;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CarePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(fn (CarePlan $record): ?string => $record->tree ? ($record->tree->species?->name.' - Petak '.$record->tree->plot?->code) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('planned_on')
                    ->label('Tanggal Rencana')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'normal', 'medium', null, '' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'low' => 'gray',
                        'normal', 'medium', null, '' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Tindakan (Ringkas)')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('assignment_info')
                    ->label('Penugasan (Opsional)')
                    ->state(function (CarePlan $record): string {
                        if (! $record->assignment) {
                            return '-(Belum ditugaskan)';
                        }
                        $code = 'TGS-'.str_pad((string) $record->assignment->id, 4, '0', STR_PAD_LEFT);
                        $assignee = $record->assignment->assignee?->name;

                        return $assignee ? "{$code} ({$assignee})" : $code;
                    })
                    ->badge()
                    ->color(fn (CarePlan $record): string => $record->assignment ? 'info' : 'gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'published', 'terbit' => 'Terbit',
                        'completed', 'selesai' => 'Selesai',
                        'cancelled', 'dibatalkan' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published', 'terbit' => 'info',
                        'completed', 'selesai' => 'success',
                        'cancelled', 'dibatalkan' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Semua Pohon')
                    ->options(fn () => Tree::query()
                        ->with('species')
                        ->get()
                        ->mapWithKeys(fn (Tree $tree) => [
                            $tree->id => $tree->code.($tree->species ? ' ('.$tree->species->name.')' : ''),
                        ])
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('site_id')
                    ->label('Lokasi')
                    ->placeholder('Semua Lokasi')
                    ->options(fn () => Site::query()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            $query->whereHas('tree.plot', fn (Builder $q) => $q->where('site_id', $data['value']));
                        }

                        return $query;
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->placeholder('Semua Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ]),

                SelectFilter::make('status')
                    ->label('Semua Status')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Terbit',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                // 1. Aksi "Lihat": Tampil sebagai tombol outline abu-abu dengan teks dan ikon mata (sesuai mockup)
                ViewAction::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs'),

                // 2. Aksi "Terbitkan": Tombol utama untuk status draf
                Action::make('publish')
                    ->label('Terbitkan')
                    ->button()
                    ->color('success')
                    ->size('xs')
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan Rencana Perawatan')
                    ->modalDescription('Apakah Anda yakin ingin menerbitkan rencana perawatan ini agar siap dieksekusi?')
                    ->modalSubmitActionLabel('Ya, Terbitkan')
                    ->visible(fn (CarePlan $record): bool => $record->status === 'draft')
                    ->action(function (CarePlan $record): void {
                        $actor = auth()->user();

                        DB::transaction(function () use ($record, $actor): void {
                            $oldStatus = $record->status;
                            $record->status = 'published';
                            $record->save();

                            $record->activityLogs()->create([
                                'actor_id' => $actor?->getKey(),
                                'action' => 'care_plan.published',
                                'from_status' => $oldStatus,
                                'to_status' => 'published',
                            ]);
                        });

                        Notification::make()
                            ->title('Rencana perawatan berhasil diterbitkan.')
                            ->success()
                            ->send();
                    }),

                // 3. Aksi "Edit": Tombol untuk rencana yang sudah terbit (koreksi tercatat dalam audit log)
                EditAction::make('edit')
                    ->label('Edit')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->using(function (CarePlan $record, array $data): CarePlan {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        return app(CorrectCarePlan::class)->handle($actor, $record, $data);
                    })
                    ->visible(fn (CarePlan $record): bool => $record->status === 'published'),

                // 4. Aksi "Edit Draf": Tombol untuk menyunting rencana yang masih berstatus draf
                EditAction::make('edit_draft')
                    ->label('Edit Draf')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->visible(fn (CarePlan $record): bool => $record->status === 'draft'),

                // 5. ActionGroup "..." (Menu Aksi Lainnya): Mengelompokkan aksi sekunder agar antarmuka ringkas & minimalis
                ActionGroup::make([
                    // Ubah Prioritas
                    Action::make('change_priority')
                        ->label('Ubah Prioritas')
                        ->icon(Heroicon::OutlinedFlag)
                        ->color('warning')
                        ->modalHeading('Ubah Prioritas Rencana Perawatan')
                        ->modalDescription('Tentukan prioritas baru dan sertakan alasan perubahan.')
                        ->modalSubmitActionLabel('Simpan Prioritas')
                        ->form([
                            Select::make('priority')
                                ->label('Tingkat Prioritas')
                                ->options([
                                    'low' => 'Rendah',
                                    'normal' => 'Normal',
                                    'high' => 'Tinggi',
                                    'urgent' => 'Mendesak',
                                ])
                                ->default(fn (CarePlan $record): string => $record->priority ?? 'normal')
                                ->required(),
                            Textarea::make('reason')
                                ->label('Alasan Perubahan')
                                ->placeholder('Contoh: Evaluasi lapangan memerlukan percepatan...')
                                ->required()
                                ->minLength(3)
                                ->maxLength(500)
                                ->rows(3)
                                ->helperText('Alasan perubahan akan tercatat dalam audit log sistem.'),
                        ])
                        ->action(function (CarePlan $record, array $data): void {
                            $actor = auth()->user();
                            abort_unless($actor instanceof User, 403);

                            app(UpdateCarePlanPriority::class)->handle($actor, $record, $data);

                            Notification::make()
                                ->title('Prioritas rencana perawatan berhasil diperbarui.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (CarePlan $record): bool => auth()->user()?->can('update', $record) && in_array($record->status, ['draft', 'published'], true)),

                    // Batalkan Rencana (AC 1 & 2 HJK-039)
                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->modalHeading('Batalkan Rencana Perawatan')
                        ->modalDescription('Rencana tidak akan dihapus. Status akan diubah menjadi Dibatalkan dan riwayat tetap tersimpan.')
                        ->modalSubmitActionLabel('Ya, Batalkan')
                        ->form([
                            Textarea::make('reason')
                                ->label('Alasan Pembatalan')
                                ->placeholder('Contoh: Kondisi pohon sudah ditangani di luar jadwal...')
                                ->required()
                                ->minLength(3)
                                ->maxLength(500)
                                ->rows(3)
                                ->helperText('Alasan wajib diisi dan akan tercatat dalam audit log sistem.'),
                        ])
                        ->action(function (CarePlan $record, array $data): void {
                            $actor = auth()->user();
                            abort_unless($actor instanceof User, 403);

                            app(CancelCarePlan::class)->handle($actor, $record, $data);

                            Notification::make()
                                ->title('Rencana perawatan berhasil dibatalkan.')
                                ->body('Status diubah menjadi Dibatalkan. Riwayat tetap tersimpan.')
                                ->warning()
                                ->send();
                        })
                        ->visible(fn (CarePlan $record): bool => in_array($record->status, ['draft', 'published'], true)),

                    // Hapus Draf (HANYA untuk status draft, TIDAK untuk rencana yang sudah berjalan/dibatalkan - AC 1 HJK-039)
                    DeleteAction::make('delete_draft')
                        ->label('Hapus Draf')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger')
                        ->modalHeading('Hapus Draf Rencana Perawatan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus draf ini secara permanen?')
                        ->modalSubmitActionLabel('Ya, Hapus Draf')
                        ->visible(fn (CarePlan $record): bool => $record->status === 'draft'),

                    // Aliasing internal untuk kompatibilitas skema lama (hidden agar tidak duplikat di menu UI)
                    DeleteAction::make('delete_more')
                        ->hidden(),
                ])
                    ->icon(Heroicon::EllipsisHorizontal)
                    ->color('gray')
                    ->size('xs')
                    ->iconButton()
                    ->tooltip('Aksi lainnya'),
            ])
            ->emptyStateHeading('Belum ada rencana perawatan')
            ->emptyStateDescription('Susun rencana perawatan pohon berkala sebelum dieksekusi di lapangan.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('planned_on', 'desc');
    }
}
