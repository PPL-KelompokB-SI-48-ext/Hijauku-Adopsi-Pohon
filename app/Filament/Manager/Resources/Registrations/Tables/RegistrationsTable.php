<?php

namespace App\Filament\Manager\Resources\Registrations\Tables;

use App\Actions\Activities\CorrectAttendance;
use App\Actions\Activities\RecordAttendance;
use App\Models\ActivityRegistration;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email Peserta')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('Status Kehadiran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registered' => 'Terdaftar (Belum Hadir)',
                        'attended' => 'Hadir',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'warning',
                        'attended' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('attended_at')
                    ->label('Waktu Kehadiran')
                    ->dateTime('d M Y, H:i \W\I\B')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu Mendaftar')
                    ->dateTime('d M Y, H:i \W\I\B')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('activity_id')
                    ->label('Kegiatan')
                    ->relationship('activity', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'registered' => 'Terdaftar (Belum Hadir)',
                        'attended' => 'Hadir',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('recordAttendance')
                        ->label('Tandai Hadir')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (ActivityRegistration $record): bool => $record->status === 'registered'
                            && $record->activity?->status !== 'cancelled'
                            && (auth()->user()?->can('recordAttendance', $record) ?? false))
                        ->modalHeading('Catat Kehadiran Peserta')
                        ->modalDescription(fn (ActivityRegistration $record): string => "Tandai kehadiran untuk {$record->user?->name} pada kegiatan \"{$record->activity?->title}\".")
                        ->modalSubmitActionLabel('Simpan Kehadiran')
                        ->form([
                            DateTimePicker::make('attended_at')
                                ->label('Waktu Kehadiran')
                                ->default(now())
                                ->maxDate(now())
                                ->native(false)
                                ->helperText('Waktu saat peserta hadir di lokasi kegiatan. Kosongkan untuk mencatat waktu sekarang.')
                                ->validationMessages([
                                    'before_or_equal' => 'Waktu kehadiran tidak boleh melebihi waktu sekarang.',
                                ]),
                            Textarea::make('notes')
                                ->label('Catatan (Opsional)')
                                ->maxLength(500)
                                ->placeholder('Contoh: Hadir tepat waktu di posko kegiatan.')
                                ->validationMessages([
                                    'max' => 'Catatan tidak boleh melebihi 500 karakter.',
                                ]),
                        ])
                        ->action(function (ActivityRegistration $record, array $data): void {
                            $actor = auth()->user();
                            abort_unless($actor instanceof User, 403);

                            app(RecordAttendance::class)->handle($actor, $record, $data);

                            Notification::make()
                                ->title('Kehadiran peserta berhasil dicatat.')
                                ->success()
                                ->send();
                        }),

                    Action::make('correctAttendance')
                        ->label('Koreksi Kehadiran')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn (ActivityRegistration $record): bool => $record->status === 'attended'
                            && (auth()->user()?->can('correctAttendance', $record) ?? false))
                        ->modalHeading('Koreksi Kehadiran Peserta')
                        ->modalDescription(fn (ActivityRegistration $record): string => "Koreksi data kehadiran untuk {$record->user?->name}. Setiap koreksi wajib disertai alasan untuk audit log.")
                        ->modalSubmitActionLabel('Simpan Koreksi')
                        ->form([
                            Select::make('status')
                                ->label('Status Kehadiran')
                                ->options([
                                    'registered' => 'Belum Hadir / Batal Hadir (Alpa)',
                                    'attended' => 'Tetap Hadir (Koreksi Jam/Waktu)',
                                ])
                                ->default('registered')
                                ->required()
                                ->native(false)
                                ->live(),
                            DateTimePicker::make('attended_at')
                                ->label('Waktu Kehadiran Terkoreksi')
                                ->default(fn (ActivityRegistration $record) => $record->attended_at)
                                ->maxDate(now())
                                ->native(false)
                                ->visible(fn ($get): bool => $get('status') === 'attended')
                                ->required(fn ($get): bool => $get('status') === 'attended')
                                ->helperText('Tentukan waktu kehadiran yang benar.')
                                ->validationMessages([
                                    'required' => 'Waktu kehadiran wajib diisi jika status tetap hadir.',
                                    'before_or_equal' => 'Waktu kehadiran tidak boleh melebihi waktu sekarang.',
                                ]),
                            Textarea::make('reason')
                                ->label('Alasan Koreksi')
                                ->required()
                                ->minLength(5)
                                ->maxLength(500)
                                ->placeholder('Contoh: Keliru menandai hadir, peserta berhalangan hadir.')
                                ->helperText('Wajib diisi dan akan dicatat dalam riwayat audit log kegiatan.')
                                ->validationMessages([
                                    'required' => 'Alasan koreksi kehadiran wajib diisi.',
                                    'min' => 'Alasan koreksi kehadiran minimal 5 karakter.',
                                    'max' => 'Alasan koreksi kehadiran tidak boleh melebihi 500 karakter.',
                                ]),
                        ])
                        ->action(function (ActivityRegistration $record, array $data): void {
                            $actor = auth()->user();
                            abort_unless($actor instanceof User, 403);

                            app(CorrectAttendance::class)->handle($actor, $record, $data);

                            Notification::make()
                                ->title('Status kehadiran peserta berhasil dikoreksi.')
                                ->success()
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Aksi Presensi'),
            ])
            ->emptyStateHeading('Belum ada data pendaftaran/kehadiran')
            ->emptyStateDescription('Peserta yang mendaftar pada kegiatan lapangan akan tampil di sini.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->defaultSort('created_at', 'desc');
    }
}
