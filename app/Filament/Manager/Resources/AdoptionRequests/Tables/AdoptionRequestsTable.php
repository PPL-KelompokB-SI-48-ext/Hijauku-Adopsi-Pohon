<?php

namespace App\Filament\Manager\Resources\AdoptionRequests\Tables;

use App\Actions\Adoptions\DecideAdoptionRequestAction;
use App\Models\AdoptionRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdoptionRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requester.name')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->description(fn (AdoptionRequest $record): ?string => $record->tree
                        ? $record->tree->species?->name
                        : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Alasan Pengajuan')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Menunggu Keputusan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('decision_reason')
                    ->label('Alasan Keputusan')
                    ->state(fn (AdoptionRequest $record): string => $record->decision_reason ?: '-')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'submitted' => 'Menunggu Keputusan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->button()
                    ->color('success')
                    ->size('xs')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Permohonan Adopsi')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui permohonan adopsi ini?')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->visible(fn (AdoptionRequest $record): bool => $record->status === 'submitted'
                        && (bool) (auth()->user()?->can('review', $record) ?? false))
                    ->action(function (AdoptionRequest $record): void {
                        $actor = auth()->user();

                        abort_unless($actor instanceof User, 403);

                        app(DecideAdoptionRequestAction::class)->handle(
                            $actor,
                            $record,
                            ['decision' => 'approved']
                        );
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->button()
                    ->color('danger')
                    ->size('xs')
                    ->modalHeading('Tolak Permohonan Adopsi')
                    ->modalDescription('Masukkan alasan penolakan permohonan adopsi.')
                    ->modalSubmitActionLabel('Tolak Permohonan')
                    ->schema([
                        Textarea::make('decision_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('Tuliskan alasan penolakan...')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->visible(fn (AdoptionRequest $record): bool => $record->status === 'submitted'
                        && (bool) (auth()->user()?->can('review', $record) ?? false))
                    ->action(function (AdoptionRequest $record, array $data): void {
                        $actor = auth()->user();

                        abort_unless($actor instanceof User, 403);

                        app(DecideAdoptionRequestAction::class)->handle(
                            $actor,
                            $record,
                            [
                                'decision' => 'rejected',
                                'decision_reason' => $data['decision_reason'],
                            ]
                        );
                    }),
            ])
            ->emptyStateHeading('Belum ada permohonan adopsi')
            ->emptyStateDescription('Permohonan adopsi dari warga akan muncul di halaman ini.')
            ->defaultSort('created_at', 'desc');
    }
}