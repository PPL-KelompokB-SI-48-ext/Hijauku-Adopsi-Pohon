<?php

namespace App\Filament\Citizen\Resources\Activities\Tables;

use App\Actions\Activities\RegisterForActivity;
use App\Models\Activity;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('kind')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'care' => 'Perawatan',
                        'planting' => 'Penanaman',
                        'maintenance' => 'Pemeliharaan',
                        'socialization' => 'Sosialisasi',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'care' => 'info',
                        'planting' => 'success',
                        'maintenance' => 'warning',
                        'socialization' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('site.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schedule')
                    ->label('Jadwal Pelaksanaan')
                    ->state(fn (Activity $record): string => $record->starts_at->format('d M Y, H:i')
                        .' - '.$record->ends_at->format('H:i').' WIB'),
                TextColumn::make('quota_info')
                    ->label('Kuota Peserta')
                    ->state(function (Activity $record): string {
                        if ($record->quota === null) {
                            return 'Tanpa batas kuota';
                        }
                        $registered = $record->registrations()->where('status', 'registered')->count();
                        return "{$registered} / {$record->quota} Peserta";
                    })
                    ->badge()
                    ->color(function (Activity $record): string {
                        if ($record->quota === null) {
                            return 'gray';
                        }
                        $registered = $record->registrations()->where('status', 'registered')->count();
                        return $registered >= $record->quota ? 'danger' : 'success';
                    }),
            ])
            ->actions([
                Action::make('register')
                    ->label(function (Activity $record): string {
                        $user = auth()->user();
                        if (! $user) {
                            return 'Daftar Kegiatan';
                        }
                        $reg = $record->registrations()->where('user_id', $user->getKey())->first();
                        if ($reg && $reg->status === 'registered') {
                            return 'Sudah Terdaftar';
                        }
                        return 'Daftar Kegiatan';
                    })
                    ->icon('heroicon-o-user-plus')
                    ->color(function (Activity $record): string {
                        $user = auth()->user();
                        if (! $user) {
                            return 'primary';
                        }
                        $reg = $record->registrations()->where('user_id', $user->getKey())->first();
                        if ($reg && $reg->status === 'registered') {
                            return 'gray';
                        }
                        return 'success';
                    })
                    ->disabled(function (Activity $record): bool {
                        $user = auth()->user();
                        if (! $user) {
                            return true;
                        }
                        $reg = $record->registrations()->where('user_id', $user->getKey())->first();
                        if ($reg && $reg->status === 'registered') {
                            return true;
                        }
                        // Jika kuota penuh
                        if ($record->quota !== null) {
                            $registered = $record->registrations()->where('status', 'registered')->count();
                            if ($registered >= $record->quota) {
                                return true;
                            }
                        }
                        return false;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pendaftaran Kegiatan')
                    ->modalDescription(fn (Activity $record): string => "Apakah Anda yakin ingin mendaftar pada kegiatan \"{$record->title}\"?")
                    ->modalSubmitActionLabel('Ya, Daftarkan Saya')
                    ->action(function (Activity $record): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(RegisterForActivity::class)->handle($actor, $record);

                        Notification::make()
                            ->title('Pendaftaran berhasil!')
                            ->body("Anda telah resmi terdaftar pada kegiatan {$record->title}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada kegiatan yang tersedia')
            ->emptyStateDescription('Saat ini belum ada agenda kegiatan lapangan terbuka untuk pendaftaran.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('starts_at');
    }
}
