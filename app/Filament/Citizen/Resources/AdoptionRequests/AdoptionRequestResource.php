<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests;

use App\Actions\Adoptions\WithdrawAdoptionRequestAction;
use App\Filament\Citizen\Resources\AdoptionRequests\Pages\CreateAdoptionRequest;
use App\Filament\Citizen\Resources\AdoptionRequests\Pages\ListAdoptionRequests;
use App\Filament\Citizen\Resources\AdoptionRequests\Pages\ViewAdoptionRequest;
use App\Filament\Citizen\Resources\AdoptionRequests\Schemas\AdoptionRequestForm;
use App\Models\AdoptionRequest;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdoptionRequestResource extends Resource
{
    protected static ?string $model = AdoptionRequest::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Permohonan Adopsi';

    protected static ?string $modelLabel = 'Permohonan Adopsi';

    protected static ?string $pluralModelLabel = 'Permohonan Adopsi';

    public static function form(Schema $schema): Schema
    {
        return AdoptionRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query->where(
                    'requester_id',
                    auth()->id()
                )
            )
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->searchable(),

                TextColumn::make('tree.species.name')
                    ->label('Jenis Pohon')
                    ->searchable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'submitted' => 'Menunggu Keputusan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'withdrawn' => 'Ditarik',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected', 'withdrawn' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),

                Action::make('withdraw')
                    ->label('Tarik Permohonan')
                    ->button()
                    ->outlined()
                    ->color('danger')
                    ->size('xs')
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Permohonan Adopsi')
                    ->modalDescription(
                        'Apakah Anda yakin ingin menarik permohonan adopsi ini? Permohonan yang sudah ditarik tidak dapat diproses kembali.'
                    )
                    ->modalSubmitActionLabel('Ya, Tarik Permohonan')
                    ->visible(
                        fn (AdoptionRequest $record): bool => $record->status === 'submitted'
                            && (bool) (
                                auth()->user()?->can('withdraw', $record) ?? false
                            )
                    )
                    ->action(function (AdoptionRequest $record): void {
                        $actor = auth()->user();

                        abort_unless($actor instanceof User, 403);

                        app(WithdrawAdoptionRequestAction::class)->handle(
                            $actor,
                            $record
                        );

                        Notification::make()
                            ->title('Permohonan berhasil ditarik.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdoptionRequests::route('/'),
            'create' => CreateAdoptionRequest::route('/create'),
            'view' => ViewAdoptionRequest::route('/{record}'),
        ];
    }
}
