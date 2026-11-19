<?php

namespace App\Filament\Citizen\Resources\Registrations\Tables;

use App\Actions\Activities\CancelActivityRegistration;
use App\Models\ActivityRegistration;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('activity.site.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity.schedule')
                    ->label('Jadwal Pelaksanaan')
                    ->state(fn (ActivityRegistration $record): string => $record->activity->starts_at->format('d M Y, H:i')
                        .' - '.$record->activity->ends_at->format('H:i').' WIB'),
                TextColumn::make('created_at')
                    ->label('Waktu Pendaftaran')
                    ->dateTime('d M Y, H:i:s \W\I\B')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status Pendaftaran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registered' => 'Terdaftar',
                        'attended' => 'Hadir',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'success',
                        'attended' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('attended_at')
                    ->label('Waktu Kehadiran')
                    ->dateTime('d M Y, H:i \W\I\B')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->actions([
                Action::make('cancelRegistration')
                    ->label('Tarik Pendaftaran')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ActivityRegistration $record): bool => $record->status === 'registered'
                        && ($record->activity?->starts_at?->isFuture() ?? false)
                        && $record->activity?->status !== 'cancelled'
                        && (auth()->user()?->can('cancel', $record) ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Pendaftaran Kegiatan')
                    ->modalDescription(fn (ActivityRegistration $record): string => "Apakah Anda yakin ingin menarik pendaftaran pada kegiatan \"{$record->activity?->title}\"? Kuota pendaftaran akan kembali tersedia untuk warga lain.")
                    ->modalSubmitActionLabel('Ya, Tarik Pendaftaran')
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Penarikan (Opsional)')
                            ->placeholder('Contoh: Berhalangan hadir karena ada keperluan mendesak.')
                            ->maxLength(500)
                            ->validationMessages([
                                'max' => 'Alasan penarikan tidak boleh melebihi 500 karakter.',
                            ]),
                    ])
                    ->action(function (ActivityRegistration $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(CancelActivityRegistration::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Pendaftaran kegiatan berhasil ditarik.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada pendaftaran kegiatan')
            ->emptyStateDescription('Anda belum mendaftar pada kegiatan lapangan manapun.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->defaultSort('created_at', 'desc');
    }
}
