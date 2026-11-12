<?php

namespace App\Filament\FieldStaff\Resources\CareRecords\Tables;

use App\Models\CareRecord;
use App\Models\FieldAssignment;
use App\Models\Tree;
use Filament\Actions\ViewAction;
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
                        'draft' => 'Draf',
                        'verified' => 'Terverifikasi',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'verified' => 'success',
                        default => 'warning',
                    }),
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
                        'draft' => 'Draf',
                        'verified' => 'Terverifikasi',
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
                    ->size('xs'),
            ])
            ->emptyStateHeading('Belum ada realisasi perawatan')
            ->emptyStateDescription('Catat tindakan perawatan yang sudah Anda laksanakan di lapangan.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->defaultSort('performed_on', 'desc');
    }
}
