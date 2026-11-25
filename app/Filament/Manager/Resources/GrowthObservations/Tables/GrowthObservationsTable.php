<?php

namespace App\Filament\Manager\Resources\GrowthObservations\Tables;

use App\Actions\GrowthObservations\ReturnGrowthObservation;
use App\Actions\GrowthObservations\VerifyGrowthObservation;
use App\Models\GrowthObservation;
use App\Models\Tree;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class GrowthObservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(fn (GrowthObservation $record): ?string => $record->tree
                        ? ($record->tree->species?->name.' - Petak '.$record->tree->plot?->code)
                        : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('observer.name')
                    ->label('Pengamat')
                    ->description('Petugas Lapangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('observed_on')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('height_cm')
                    ->label('Tinggi (cm)')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', '.').' cm')
                    ->sortable(),

                TextColumn::make('diameter_cm')
                    ->label('Diameter (cm)')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', '.').' cm')
                    ->sortable(),

                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => GrowthObservation::conditionLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        GrowthObservation::CONDITION_HEALTHY => 'success',
                        GrowthObservation::CONDITION_FAIR => 'info',
                        GrowthObservation::CONDITION_NEEDS_RECOVERY => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

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
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->options([
                        'submitted' => 'Diajukan',
                        'verified' => 'Terverifikasi',
                        'returned' => 'Dikembalikan',
                        'draft' => 'Draf',
                    ]),

                SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->placeholder('Semua Kondisi')
                    ->options(GrowthObservation::conditionOptions()),

                SelectFilter::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Semua Pohon')
                    ->options(fn (): array => Tree::query()
                        ->with('species')
                        ->orderBy('code')
                        ->get()
                        ->mapWithKeys(fn (Tree $tree): array => [
                            $tree->id => $tree->code.($tree->species ? ' ('.$tree->species->name.')' : ''),
                        ])
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                ViewAction::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->modalHeading('Detail Pengamatan Pertumbuhan')
                    ->form([
                        TextInput::make('tree_info')
                            ->label('Pohon')
                            ->formatStateUsing(fn (GrowthObservation $record): string => $record->tree
                                ? $record->tree->code.' ('.($record->tree->species?->name ?? '-').', Petak '.($record->tree->plot?->code ?? '-').')'
                                : '-')
                            ->disabled(),

                        TextInput::make('observer_info')
                            ->label('Pengamat')
                            ->formatStateUsing(fn (GrowthObservation $record): string => ($record->observer?->name ?? '-').' (Petugas Lapangan)')
                            ->disabled(),

                        DatePicker::make('observed_on')
                            ->label('Tanggal Pengamatan')
                            ->disabled(),

                        TextInput::make('height_info')
                            ->label('Tinggi Pohon')
                            ->formatStateUsing(fn (GrowthObservation $record): string => number_format((float) $record->height_cm, 2, ',', '.').' cm')
                            ->disabled(),

                        TextInput::make('diameter_info')
                            ->label('Diameter Batang')
                            ->formatStateUsing(fn (GrowthObservation $record): string => number_format((float) $record->diameter_cm, 2, ',', '.').' cm')
                            ->disabled(),

                        TextInput::make('condition_info')
                            ->label('Kondisi Fisik Pohon')
                            ->formatStateUsing(fn (GrowthObservation $record): string => GrowthObservation::conditionLabel($record->condition))
                            ->disabled(),

                        TextInput::make('status_info')
                            ->label('Status Saat Ini')
                            ->formatStateUsing(fn (GrowthObservation $record): string => match ($record->status) {
                                'submitted' => 'Diajukan (Menunggu verifikasi pengelola)',
                                'verified' => 'Terverifikasi',
                                'returned' => 'Dikembalikan',
                                'draft' => 'Draf',
                                default => ucfirst($record->status),
                            })
                            ->disabled(),

                        TextInput::make('verifier_info')
                            ->label('Diverifikasi / Ditinjau Oleh')
                            ->formatStateUsing(fn (GrowthObservation $record): string => $record->verifier
                                ? $record->verifier->name.' pada '.($record->verified_at?->format('d M Y H:i') ?? '-')
                                : '-')
                            ->disabled()
                            ->visible(fn (GrowthObservation $record): bool => ! empty($record->verified_by)),

                        Placeholder::make('photo_download')
                            ->label('Bukti Foto Privat')
                            ->columnSpanFull()
                            ->visible(fn (GrowthObservation $record): bool => ! empty($record->photo_path))
                            ->content(fn (GrowthObservation $record): HtmlString => new HtmlString(
                                '<a href="'.e(route('growth-observations.photo', $record)).'" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">Unduh Bukti Foto</a>'
                            )),

                        Textarea::make('review_note')
                            ->label('Catatan Evaluasi / Alasan Pengembalian')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn (GrowthObservation $record): bool => ! empty($record->review_note)),

                        Placeholder::make('audit_trail')
                            ->label('Riwayat Audit & Verifikasi')
                            ->columnSpanFull()
                            ->content(function (GrowthObservation $record): HtmlString {
                                $logs = $record->getAuditSummary();
                                if (empty($logs)) {
                                    return new HtmlString('<p class="text-xs text-gray-500 italic">Belum ada riwayat audit tercatat.</p>');
                                }

                                $html = '<div class="space-y-2 mt-1">';
                                foreach ($logs as $log) {
                                    $noteHtml = $log['note']
                                        ? '<div class="text-xs mt-1 text-gray-600 bg-gray-50 p-2 rounded border border-gray-100"><strong>Catatan:</strong> '.e($log['note']).'</div>'
                                        : '';

                                    $html .= sprintf(
                                        '<div class="p-2.5 rounded-lg border border-gray-200 bg-white text-xs">
                                            <div class="flex items-center justify-between text-gray-500 mb-1">
                                                <span class="font-medium text-gray-700">%s (%s)</span>
                                                <span>%s</span>
                                            </div>
                                            <div class="font-semibold text-gray-800">%s</div>
                                            %s
                                        </div>',
                                        e($log['actor']),
                                        e($log['role']),
                                        e($log['time']),
                                        e($log['action']),
                                        $noteHtml,
                                    );
                                }
                                $html .= '</div>';

                                return new HtmlString($html);
                            }),
                    ]),

                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon(Heroicon::OutlinedCheck)
                    ->button()
                    ->color('success')
                    ->size('xs')
                    ->visible(fn (GrowthObservation $record): bool => auth()->user()?->can('verify', $record) && $record->status === 'submitted')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pengamatan Pertumbuhan')
                    ->modalDescription('Hanya pengamatan terverifikasi yang akan dimasukkan ke ringkasan dan grafik perkembangan pohon.')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->action(function (GrowthObservation $record): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(VerifyGrowthObservation::class)->handle($actor, $record);

                        Notification::make()
                            ->title('Pengamatan pertumbuhan berhasil diverifikasi.')
                            ->success()
                            ->send();
                    }),

                Action::make('return')
                    ->label('Kembalikan')
                    ->icon(Heroicon::OutlinedXMark)
                    ->button()
                    ->outlined()
                    ->color('danger')
                    ->size('xs')
                    ->visible(fn (GrowthObservation $record): bool => auth()->user()?->can('verify', $record) && $record->status === 'submitted')
                    ->modalHeading('Kembalikan Pengamatan Pertumbuhan')
                    ->modalDescription('Tuliskan alasan pengembalian agar petugas dapat memeriksa ulang pengukuran atau bukti foto.')
                    ->modalSubmitActionLabel('Kembalikan Laporan')
                    ->form([
                        Textarea::make('review_note')
                            ->label('Alasan Pengembalian')
                            ->placeholder('Contoh: Angka diameter tidak konsisten dengan pengukuran bulan lalu, mohon ukur ulang...')
                            ->required()
                            ->minLength(5)
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Alasan wajib diisi dan dapat dibaca langsung oleh petugas lapangan.'),
                    ])
                    ->action(function (GrowthObservation $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(ReturnGrowthObservation::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Pengamatan pertumbuhan dikembalikan kepada petugas.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada pengamatan pertumbuhan')
            ->emptyStateDescription('Pengamatan terukur yang dicatat oleh petugas lapangan akan muncul di sini untuk diverifikasi.')
            ->emptyStateIcon('heroicon-o-arrow-trending-up')
            ->defaultPaginationPageOption(20)
            ->defaultSort('observed_on', 'desc');
    }
}
