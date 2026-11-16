<?php

namespace App\Filament\FieldStaff\Resources\CareRecords\Tables;

use App\Models\CareRecord;
use App\Models\FieldAssignment;
use App\Models\Tree;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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

                TextColumn::make('performed_on')
                    ->label('Tanggal Pelaksanaan')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Tindakan (Ringkas)')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('plan.planned_on')
                    ->label('Berdasarkan Rencana')
                    ->date('d M Y')
                    ->placeholder('-(Langsung/tanpa rencana)')
                    ->badge()
                    ->color('info'),

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
                    ->description(fn (CareRecord $record): ?string => $record->status === 'returned' && $record->review_note
                        ? 'Alasan: '.Str::limit($record->review_note, 35)
                        : null),
            ])
            ->filters([
                SelectFilter::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Semua Pohon')
                    ->options(function (): array {
                        $user = auth()->user();

                        if (! $user) {
                            return [];
                        }

                        $assignedTreeIds = FieldAssignment::query()
                            ->where('assignee_id', $user->getKey())
                            ->pluck('tree_id')
                            ->unique()
                            ->all();

                        return Tree::query()
                            ->whereIn('id', $assignedTreeIds)
                            ->with('species')
                            ->get()
                            ->mapWithKeys(fn (Tree $tree) => [
                                $tree->id => $tree->code.($tree->species ? ' ('.$tree->species->name.')' : ''),
                            ])
                            ->all();
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->options([
                        'submitted' => 'Diajukan',
                        'verified' => 'Terverifikasi',
                        'returned' => 'Dikembalikan',
                        'draft' => 'Draf',
                    ]),
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
                    ->modalHeading('Detail Realisasi Perawatan')
                    ->form([
                        TextInput::make('tree_info')
                            ->label('Pohon')
                            ->formatStateUsing(fn (CareRecord $record): string => $record->tree
                                ? $record->tree->code.' ('.($record->tree->species?->name ?? '-').', Petak '.($record->tree->plot?->code ?? '-').')'
                                : '-')
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
                            ->label('Status')
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
                            ->label('Catatan Evaluasi / Alasan Pengembalian dari Pengelola')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn (CareRecord $record): bool => ! empty($record->review_note)),
                    ]),
            ])
            ->emptyStateHeading('Belum ada realisasi perawatan')
            ->emptyStateDescription('Catat tindakan perawatan yang sudah Anda laksanakan di lapangan.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->defaultSort('performed_on', 'desc');
    }
}
