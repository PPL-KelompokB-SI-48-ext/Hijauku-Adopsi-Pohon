<?php

namespace App\Filament\Manager\Resources\AdoptionAgreements\Tables;

use App\Actions\Adoptions\TerminateAdoptionAgreementAction;
use App\Models\AdoptionAgreement;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdoptionAgreementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(
                        fn (AdoptionAgreement $record): string => $record->tree->species->name
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('adopter.name')
                    ->label('Pengadopsi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period')
                    ->label('Periode')
                    ->state(
                        fn (AdoptionAgreement $record): string => $record->starts_on->format('d/m/Y')
                            .' - '.($record->ends_on?->format('d/m/Y') ?? '-')
                    ),

                TextColumn::make('status')
                    ->label('Status Kesepakatan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'awaiting_acceptance' => 'Menunggu Warga',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'terminated' => 'Dihentikan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'awaiting_acceptance' => 'warning',
                        'active' => 'success',
                        'completed' => 'info',
                        'terminated' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Kesepakatan')
                    ->placeholder('Semua Status')
                    ->options([
                        'awaiting_acceptance' => 'Menunggu Warga',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'terminated' => 'Dihentikan',
                    ])
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->recordActions([
                Action::make('terminate')
                    ->label('Akhiri Kesepakatan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AdoptionAgreement $record): bool => $record->status === 'active')
                    ->modalHeading('Akhiri Kesepakatan Adopsi')
                    ->modalDescription('Penghentian lebih awal akan membuat pohon tersedia kembali untuk adopsi.')
                    ->modalSubmitActionLabel('Akhiri Kesepakatan')
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Penghentian')
                            ->placeholder('Tuliskan alasan penghentian kesepakatan.')
                            ->required()
                            ->minLength(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (AdoptionAgreement $record, array $data): void {
                        $actor = auth()->user();

                        abort_unless($actor instanceof User, 403);

                        app(TerminateAdoptionAgreementAction::class)->handle(
                            $actor,
                            $record,
                            $data
                        );
                    })
                    ->successNotificationTitle('Kesepakatan berhasil diakhiri.'),
            ])
            ->emptyStateHeading('Belum ada kesepakatan adopsi')
            ->emptyStateDescription(
                'Buat kesepakatan dari permohonan adopsi yang telah disetujui.'
            )
            ->emptyStateIcon('heroicon-o-document-text')
            ->defaultSort('created_at', 'desc');
    }
}
