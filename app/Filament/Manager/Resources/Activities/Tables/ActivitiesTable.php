<?php

namespace App\Filament\Manager\Resources\Activities\Tables;

use App\Actions\Activities\OpenActivity;
use App\Actions\Activities\UpdateActivityQuota;
use App\Models\Activity;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
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
                    ->label('Judul Kegiatan')
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
                TextColumn::make('quota')
                    ->label('Kuota Peserta')
                    ->state(fn (Activity $record): string => $record->quota ? "{$record->quota} Peserta" : 'Tanpa kuota'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'open' => 'Terbuka',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'open' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'info',
                    }),
            ])
            ->actions([
                Action::make('open')
                    ->label('Buka Kegiatan')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (Activity $record): bool => auth()->user()?->can('open', $record) ?? false)
                    ->modalHeading('Buka Pendaftaran Kegiatan')
                    ->modalDescription('Ubah status kegiatan menjadi Terbuka agar pendaftaran dapat diakses oleh warga.')
                    ->modalSubmitActionLabel('Buka Kegiatan')
                    ->form(fn (Activity $record): array => [
                        TextInput::make('quota')
                            ->label('Batas kuota peserta')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->default($record->quota)
                            ->helperText('Tentukan batas kuota peserta. Kosongkan jika tanpa batasan kuota.')
                            ->validationMessages([
                                'numeric' => 'Kuota berupa bilangan positif bila diisi.',
                                'min' => 'Kuota berupa bilangan positif bila diisi.',
                            ]),
                        Checkbox::make('confirm_quota_change')
                            ->label('Saya mengonfirmasi perubahan kuota kegiatan ini.')
                            ->visible(fn (): bool => $record->registrations()->where('status', '!=', 'cancelled')->count() > 0)
                            ->helperText(fn (): string => 'Perhatian: Kegiatan ini sudah memiliki '
                                . $record->registrations()->where('status', '!=', 'cancelled')->count()
                                . ' pendaftar. Konfirmasi diperlukan untuk mengubah kuota.'),
                    ])
                    ->action(function (Activity $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(OpenActivity::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Kegiatan berhasil dibuka.')
                            ->success()
                            ->send();
                    }),

                Action::make('updateQuota')
                    ->label('Ubah Kuota')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('gray')
                    ->visible(fn (Activity $record): bool => $record->status === 'open' && (auth()->user()?->can('updateQuota', $record) ?? false))
                    ->modalHeading('Ubah Kuota Peserta')
                    ->modalDescription('Sesuaikan batas kuota peserta untuk kegiatan yang sedang dibuka ini.')
                    ->modalSubmitActionLabel('Simpan Kuota')
                    ->form(fn (Activity $record): array => [
                        TextInput::make('quota')
                            ->label('Kuota peserta')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->default($record->quota)
                            ->helperText('Isi bilangan positif atau kosongkan jika tanpa batas kuota.')
                            ->validationMessages([
                                'numeric' => 'Kuota berupa bilangan positif bila diisi.',
                                'min' => 'Kuota berupa bilangan positif bila diisi.',
                            ]),
                        Checkbox::make('confirm_quota_change')
                            ->label('Saya mengonfirmasi perubahan kuota kegiatan ini.')
                            ->visible(fn (): bool => $record->registrations()->where('status', '!=', 'cancelled')->count() > 0)
                            ->helperText(fn (): string => 'Perhatian: Kegiatan ini sudah memiliki '
                                . $record->registrations()->where('status', '!=', 'cancelled')->count()
                                . ' pendaftar. Konfirmasi diperlukan untuk mengubah kuota.'),
                    ])
                    ->action(function (Activity $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(UpdateActivityQuota::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Kuota kegiatan berhasil diperbarui.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada kegiatan lapangan')
            ->emptyStateDescription('Tambahkan kegiatan lapangan baru untuk mulai menjadwalkan aksi di lokasi.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('starts_at');
    }
}
