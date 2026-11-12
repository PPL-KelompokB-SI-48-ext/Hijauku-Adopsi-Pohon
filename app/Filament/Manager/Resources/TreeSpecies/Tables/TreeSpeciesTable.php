<?php

namespace App\Filament\Manager\Resources\TreeSpecies\Tables;

use App\Actions\TreeSpecies\DeactivateTreeSpecies;
use App\Actions\TreeSpecies\DeleteTreeSpecies;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class TreeSpeciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Jenis Pohon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('care_notes')
                    ->label('Catatan Perawatan')
                    ->limit(80)
                    ->wrap()
                    ->default('-'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('trees_count')
                    ->label('Pohon Terdaftar')
                    ->counts('trees')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Didaftarkan Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                // 1. Edit: buka halaman edit untuk memperbarui nama dan catatan perawatan
                EditAction::make()
                    ->label('Edit')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs'),

                // 2. Nonaktifkan: tersedia selama jenis masih aktif
                Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->button()
                    ->outlined()
                    ->color('warning')
                    ->size('xs')
                    ->icon(Heroicon::OutlinedPause)
                    ->visible(fn (TreeSpecies $record): bool => (bool) $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan Jenis Pohon')
                    ->modalDescription(fn (TreeSpecies $record): string => "Nonaktifkan \"{$record->name}\"? Jenis ini tidak akan bisa dipilih pada inventaris baru, namun data historis tetap aman.")
                    ->modalSubmitActionLabel('Ya, Nonaktifkan')
                    ->action(function (TreeSpecies $record): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(DeactivateTreeSpecies::class)->handle($actor, $record);
                    })
                    ->successNotificationTitle('Jenis pohon berhasil dinonaktifkan.'),

                // 3. Hapus: hanya tersedia jika belum ada pohon terdaftar
                DeleteAction::make()
                    ->label('Hapus')
                    ->button()
                    ->outlined()
                    ->color('danger')
                    ->size('xs')
                    ->visible(fn (TreeSpecies $record): bool => $record->trees_count === 0)
                    ->modalHeading('Hapus Jenis Pohon')
                    ->modalDescription(fn (TreeSpecies $record): string => "Hapus \"{$record->name}\" secara permanen? Tindakan ini tidak dapat dibatalkan.")
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->using(function (TreeSpecies $record): bool {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        try {
                            app(DeleteTreeSpecies::class)->handle($actor, $record);

                            return true;
                        } catch (ValidationException $e) {
                            return false;
                        }
                    }),
            ])
            ->emptyStateHeading('Belum ada jenis pohon')
            ->emptyStateDescription('Tambahkan jenis pohon baru untuk mulai mencatat acuan katalog pohon.')
            ->emptyStateIcon('heroicon-o-tag')
            ->defaultSort('name');
    }
}
