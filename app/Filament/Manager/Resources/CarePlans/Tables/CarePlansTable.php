<?php

namespace App\Filament\Manager\Resources\CarePlans\Tables;

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
                // 1. Aksi "Lihat": Tampil sebagai tombol outline abu-abu lembut dengan ikon mata
                ViewAction::make()
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs'),

                // 2. Aksi "Edit": Untuk rencana yang sudah Terbit (published)
                EditAction::make('edit')
                    ->label('Edit')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->visible(fn (CarePlan $record): bool => $record->status === 'published'),

                // 3. Aksi "Edit Draf": Untuk rencana yang masih Draf
                EditAction::make('edit_draft')
                    ->label('Edit Draf')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->visible(fn (CarePlan $record): bool => $record->status === 'draft'),

                // 4. Aksi "Terbitkan": Tombol hijau lembut/outline terang untuk Draf
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
                    }),

                // 5. Aksi "Ubah Prioritas": Tombol dengan modal untuk mengubah tingkat prioritas disertai alasan
                Action::make('change_priority')
                    ->label('Ubah Prioritas')
                    ->button()
                    ->outlined()
                    ->color('warning')
                    ->size('xs')
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
                    ->visible(fn (CarePlan $record): bool => auth()->user()?->can('update', $record) && $record->status !== 'cancelled'),

                // 5. Aksi "Hapus Draf": Tombol outline merah lembut untuk draf yang dibatalkan / draft
                DeleteAction::make('delete_draft')
                    ->label('Hapus Draf')
                    ->button()
                    ->outlined()
                    ->color('danger')
                    ->size('xs')
                    ->modalHeading('Hapus Draf Rencana Perawatan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus draf rencana perawatan ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->visible(fn (CarePlan $record): bool => $record->status === 'draft' || $record->status === 'cancelled'),

                // 6. ActionGroup "..." (More / Ellipsis): Hanya tampil jika ada aksi anak yang aktif (status draft / dibatalkan)
                ActionGroup::make([
                    DeleteAction::make('delete_more')
                        ->label('Hapus Rencana')
                        ->visible(fn (CarePlan $record): bool => $record->status === 'draft' || $record->status === 'cancelled'),
                ])
                    ->icon(Heroicon::EllipsisHorizontal)
                    ->color('gray')
                    ->size('xs')
                    ->iconButton()
                    ->tooltip('Aksi lainnya')
                    ->visible(fn (CarePlan $record): bool => $record->status === 'draft' || $record->status === 'cancelled'),
            ])
            ->emptyStateHeading('Belum ada rencana perawatan')
            ->emptyStateDescription('Susun rencana perawatan pohon berkala sebelum dieksekusi di lapangan.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('planned_on', 'desc');
    }
}
