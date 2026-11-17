<?php

namespace App\Filament\Manager\Resources\CareRecords\Tables;

use App\Actions\CareRecords\CorrectCareRecord;
use App\Actions\CareRecords\ReturnCareRecord;
use App\Actions\CareRecords\VerifyCareRecord;
use App\Models\CareRecord;
use App\Models\Tree;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CareRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(fn (CareRecord $record): ?string => $record->tree
                        ? ($record->tree->species?->name.' - Petak '.$record->tree->plot?->code)
                        : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('performer.name')
                    ->label('Pelaksana')
                    ->description('Petugas Lapangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('performed_on')
                    ->label('Tanggal Pelaksanaan')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Tindakan (Ringkas)')
                    ->limit(60)
                    ->wrap()
                    ->description(fn (CareRecord $record): ?string => $record->photo_path ? '📷 Ada bukti foto' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted', 'diajukan' => 'Diajukan',
                        'verified', 'terverifikasi' => 'Terverifikasi',
                        'returned', 'dikembalikan' => 'Dikembalikan',
                        'draft' => 'Draf',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'submitted', 'diajukan' => 'warning',
                        'verified', 'terverifikasi' => 'success',
                        'returned', 'dikembalikan' => 'danger',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
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

                SelectFilter::make('status')
                    ->label('Semua Status')
                    ->options([
                        'submitted' => 'Diajukan',
                        'verified' => 'Terverifikasi',
                        'returned' => 'Dikembalikan',
                        'draft' => 'Draf',
                    ]),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                // 1. Aksi Lihat
                ViewAction::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->modalHeading('Detail Realisasi Perawatan')
                    ->form([
                        TextInput::make('tree_info')
                            ->label('Pohon')
                            ->formatStateUsing(fn (CareRecord $record): string => $record->tree
                                ? $record->tree->code.' ('.($record->tree->species?->name ?? '-').', Petak '.($record->tree->plot?->code ?? '-').')'
                                : '-')
                            ->disabled(),

                        TextInput::make('performer_info')
                            ->label('Pelaksana')
                            ->formatStateUsing(fn (CareRecord $record): string => ($record->performer?->name ?? '-').' (Petugas Lapangan)')
                            ->disabled(),

                        DatePicker::make('performed_on')
                            ->label('Tanggal Pelaksanaan')
                            ->disabled(),

                        TextInput::make('plan_info')
                            ->label('Rujukan Rencana Perawatan')
                            ->formatStateUsing(fn (CareRecord $record): string => $record->plan
                                ? ($record->plan->planned_on?->format('d M Y') ?? '-').' - '.$record->plan->action
                                : 'Tanpa rencana (Perawatan langsung)')
                            ->disabled(),

                        Textarea::make('action')
                            ->label('Tindakan yang Dilaksanakan')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),

                        FileUpload::make('photo_path')
                            ->label('Bukti Foto')
                            ->disk('private')
                            ->visibility('private')
                            ->image()
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn (CareRecord $record): bool => ! empty($record->photo_path)),

                        TextInput::make('status_info')
                            ->label('Status Saat Ini')
                            ->formatStateUsing(fn (CareRecord $record): string => match ($record->status) {
                                'submitted' => 'Diajukan (Menunggu verifikasi pengelola)',
                                'verified' => 'Terverifikasi',
                                'returned' => 'Dikembalikan',
                                'draft' => 'Draf',
                                default => ucfirst($record->status),
                            })
                            ->disabled(),

                        TextInput::make('verifier_info')
                            ->label('Diverifikasi / Ditinjau Oleh')
                            ->formatStateUsing(fn (CareRecord $record): string => $record->verifier
                                ? $record->verifier->name.' pada '.($record->verified_at?->format('d M Y H:i') ?? '-')
                                : '-')
                            ->disabled()
                            ->visible(fn (CareRecord $record): bool => ! empty($record->verified_by)),

                        Textarea::make('review_note')
                            ->label('Catatan Evaluasi / Alasan Pengembalian')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn (CareRecord $record): bool => ! empty($record->review_note)),
                    ]),

                // 2. Aksi Verifikasi (AC 1 & AC 3)
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon(Heroicon::OutlinedCheck)
                    ->button()
                    ->color('success')
                    ->size('xs')
                    ->visible(fn (CareRecord $record): bool => auth()->user()?->can('verify', $record) && $record->status === 'submitted')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Realisasi Perawatan')
                    ->modalDescription('Apakah Anda yakin hasil pelaksanaan perawatan pohon ini sudah sesuai dan dapat diverifikasi?')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->action(function (CareRecord $record): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(VerifyCareRecord::class)->handle($actor, $record);

                        Notification::make()
                            ->title('Realisasi perawatan berhasil diverifikasi.')
                            ->success()
                            ->send();
                    }),

                // 3. Aksi Kembalikan / Tolak (AC 1, AC 2 & AC 3, HJK-042: Realisasi bermasalah)
                Action::make('return')
                    ->label('Kembalikan')
                    ->icon(Heroicon::OutlinedXMark)
                    ->button()
                    ->outlined()
                    ->color('danger')
                    ->size('xs')
                    ->visible(fn (CareRecord $record): bool => auth()->user()?->can('verify', $record) && in_array($record->status, ['submitted', 'verified'], true))
                    ->modalHeading('Kembalikan Realisasi Perawatan')
                    ->modalDescription('Tuliskan catatan alasan pengembalian/penolakan agar petugas dapat membaca dan memperbaiki laporan.')
                    ->modalSubmitActionLabel('Kembalikan Laporan')
                    ->form([
                        Textarea::make('review_note')
                            ->label('Alasan Penolakan / Catatan Perbaikan')
                            ->placeholder('Contoh: Foto dokumentasi kurang jelas, harap lampirkan foto dari sudut yang memperlihatkan kondisi pangkal pohon...')
                            ->required()
                            ->minLength(5)
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Alasan wajib diisi dan akan dapat dibaca langsung oleh petugas di panel tugasnya.'),
                    ])
                    ->action(function (CareRecord $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(ReturnCareRecord::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Realisasi perawatan dikembalikan kepada petugas.')
                            ->warning()
                            ->send();
                    }),

                // 4. Aksi Koreksi (HJK-042 AC 1: Data terverifikasi dikoreksi via keputusan tercatat)
                Action::make('correct')
                    ->label('Koreksi')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->visible(fn (CareRecord $record): bool => auth()->user()?->can('update', $record) && $record->status === 'verified')
                    ->modalHeading('Koreksi Realisasi Perawatan Terverifikasi')
                    ->modalDescription('Perubahan data terverifikasi wajib menyertakan alasan keputusan dan akan dicatat dalam riwayat audit.')
                    ->modalSubmitActionLabel('Simpan Keputusan Koreksi')
                    ->form([
                        DatePicker::make('performed_on')
                            ->label('Tanggal Pelaksanaan')
                            ->default(fn (CareRecord $record) => $record->performed_on?->format('Y-m-d'))
                            ->required(),

                        Textarea::make('action')
                            ->label('Tindakan yang Dilaksanakan')
                            ->default(fn (CareRecord $record) => $record->action)
                            ->required()
                            ->minLength(3)
                            ->maxLength(1000)
                            ->rows(3),

                        Textarea::make('reason')
                            ->label('Alasan Keputusan Koreksi')
                            ->placeholder('Contoh: Penyesuaian deskripsi volume pupuk sesuai bukti nota lapangan...')
                            ->required()
                            ->minLength(5)
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Alasan keputusan wajib diisi dan akan dicatat dalam audit log sistem (bukan edit diam-diam).'),
                    ])
                    ->action(function (CareRecord $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(CorrectCareRecord::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Data realisasi perawatan berhasil dikoreksi dengan keputusan tercatat.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada realisasi perawatan')
            ->emptyStateDescription('Bukti perawatan pohon yang dicatat oleh petugas akan muncul di sini untuk diverifikasi.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->defaultSort('performed_on', 'desc');
    }
}
