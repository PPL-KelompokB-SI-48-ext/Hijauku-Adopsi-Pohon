<?php

namespace App\Filament\Citizen\Resources\AdoptionAgreements;

use App\Actions\Adoptions\AcceptAdoptionAgreementAction;
use App\Filament\Citizen\Resources\AdoptionAgreements\Pages\ListAdoptionAgreements;
use App\Models\AdoptionAgreement;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AdoptionAgreementResource extends Resource
{
    protected static ?string $model = AdoptionAgreement::class;

    protected static ?string $navigationLabel = 'Kesepakatan Saya';

    protected static ?string $modelLabel = 'Kesepakatan Adopsi';

    protected static ?string $pluralModelLabel = 'Kesepakatan Saya';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('adopter_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->searchable(),

                TextColumn::make('tree.species.name')
                    ->label('Jenis Pohon')
                    ->searchable(),

                TextColumn::make('starts_on')
                    ->label('Mulai Adopsi')
                    ->date('d/m/Y'),

                TextColumn::make('ends_on')
                    ->label('Selesai Adopsi')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextColumn::make('terms')
                    ->label('Ketentuan')
                    ->limit(60),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'awaiting_acceptance' => 'Menunggu Persetujuan',
                        'active' => 'Aktif',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'awaiting_acceptance' => 'warning',
                        'active' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('accepted_at')
                    ->label('Diterima Pada')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('Terima Kesepakatan')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Terima Kesepakatan Adopsi')
                    ->modalDescription('Apakah kamu yakin ingin menerima kesepakatan adopsi ini?')
                    ->modalSubmitActionLabel('Terima Kesepakatan')
                    ->visible(fn (AdoptionAgreement $record): bool =>
                        $record->status === 'awaiting_acceptance'
                        && auth()->user()?->can('accept', $record)
                    )
                    ->action(function (AdoptionAgreement $record): void {
                        try {
                            app(AcceptAdoptionAgreementAction::class)
                                ->handle(auth()->user(), $record);

                            Notification::make()
                                ->title('Kesepakatan berhasil diterima')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            throw $exception;
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdoptionAgreements::route('/'),
        ];
    }
}
