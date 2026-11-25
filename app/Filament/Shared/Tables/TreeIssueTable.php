<?php

namespace App\Filament\Shared\Tables;

use App\Models\TreeIssue;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreeIssueTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Masalah')
                    ->limit(70)
                    ->wrap(),

                TextColumn::make('severity')
                    ->label('Penanganan')
                    ->formatStateUsing(fn (string $state): string => TreeIssue::SEVERITY_OPTIONS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'normal' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Dilaporkan',
                        'in_progress' => 'Ditangani',
                        'rejected' => 'Ditolak',
                        default => str($state)->headline()->toString(),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'in_progress' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dilaporkan')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('downloadEvidence')
                    ->label('Unduh Bukti')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (TreeIssue $record): string => route('tree-issues.evidence', $record))
                    ->visible(fn (TreeIssue $record): bool => auth()->user()?->can('downloadEvidence', $record) ?? false),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 20, 50])
            ->defaultPaginationPageOption(20)
            ->emptyStateHeading('Belum ada laporan masalah pohon')
            ->emptyStateDescription('Laporan yang Anda kirim akan tampil di sini.');
    }
}
