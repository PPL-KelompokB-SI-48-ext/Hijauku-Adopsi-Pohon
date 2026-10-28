<?php

namespace App\Filament\Manager\Resources\CarePlans\Tables;

use App\Models\CarePlan;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class CarePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(fn (CarePlan $record): ?string => $record->tree ? ($record->tree->species?->name . ' - Petak ' . $record->tree->plot?->code) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('planned_on')
                    ->label('Tanggal Rencana')
                    ->date('d M Y')
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
                        $code = 'TGS-' . str_pad((string) $record->assignment->id, 4, '0', STR_PAD_LEFT);
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
