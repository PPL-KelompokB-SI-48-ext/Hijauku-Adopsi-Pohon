<?php

namespace App\Filament\Manager\Resources\TreeIssues\Tables;

use App\Actions\TreeIssues\HandleTreeIssue;
use App\Actions\TreeIssues\RejectTreeIssue;
use App\Enums\UserRole;
use App\Models\TreeIssue;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TreeIssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Masalah')
                    ->limit(70)
                    ->wrap(),

                TextColumn::make('severity')
                    ->label('Tingkat Penanganan')
                    ->formatStateUsing(fn (string $state): string => TreeIssue::SEVERITY_OPTIONS[$state] ?? $state)
                    ->badge(),

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

                TextColumn::make('assignee.name')
                    ->label('Petugas Tindak Lanjut')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('resolution')
                    ->label('Alasan Penolakan')
                    ->placeholder('-')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Dilaporkan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'submitted' => 'Dilaporkan',
                        'in_progress' => 'Ditangani',
                        'rejected' => 'Ditolak',
                    ])
                    ->native(false),
            ])
            ->recordActionsColumnLabel('Tindak Lanjut')
            ->recordActions([
                Action::make('handle')
                    ->label('Tugaskan dan Tangani')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tugaskan Tindak Lanjut')
                    ->modalDescription('Pilih petugas lapangan untuk menangani laporan ini.')
                    ->modalSubmitActionLabel('Tugaskan dan Tandai Ditangani')
                    ->successNotificationTitle('Laporan ditugaskan dan ditandai ditangani.')
                    ->schema([
                        Select::make('assignee_id')
                            ->label('Petugas Tindak Lanjut')
                            ->options(fn (): array => User::query()
                                ->where('role', UserRole::FieldStaff->value)
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->required()
                            ->native(),
                    ])
                    ->visible(fn (TreeIssue $record): bool => $record->status === 'submitted'
                        && (bool) (auth()->user()?->can('handle', $record) ?? false))
                    ->action(function (TreeIssue $record, array $data): void {
                        $actor = auth()->user();

                        abort_unless($actor instanceof User, 403);

                        app(HandleTreeIssue::class)->handle($actor, $record, $data);
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->button()
                    ->color('danger')
                    ->modalHeading('Tolak Laporan Masalah')
                    ->modalDescription('Masukkan alasan penolakan laporan.')
                    ->modalSubmitActionLabel('Tolak Laporan')
                    ->successNotificationTitle('Laporan ditolak.')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->helperText('Alasan ini disimpan dalam riwayat laporan.')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->visible(fn (TreeIssue $record): bool => $record->status === 'submitted'
                        && (bool) (auth()->user()?->can('reject', $record) ?? false))
                    ->action(function (TreeIssue $record, array $data): void {
                        $actor = auth()->user();

                        abort_unless($actor instanceof User, 403);

                        app(RejectTreeIssue::class)->handle($actor, $record, $data['reason']);
                    }),

                Action::make('downloadEvidence')
                    ->label('Unduh Bukti')
                    ->url(fn (TreeIssue $record): string => route('tree-issues.evidence', $record))
                    ->visible(fn (TreeIssue $record): bool => $record->evidence_path !== null
                        && (bool) (auth()->user()?->can('downloadEvidence', $record) ?? false)),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 20, 50])
            ->defaultPaginationPageOption(20)
            ->emptyStateHeading('Belum ada laporan masalah pohon')
            ->emptyStateDescription('Laporan warga dan petugas akan muncul di halaman ini.');
    }
}
