<?php

namespace App\Filament\Manager\Resources\Sites\Tables;

use App\Actions\Sites\DeleteUnusedSite;
use App\Actions\Sites\UpdateSite;
use App\Filament\Manager\Resources\Sites\Schemas\SiteForm;
use App\Models\Site;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->description(fn (Site $record): string => 'ID: #'.$record->getKey())
                    ->icon(Heroicon::OutlinedMapPin)
                    ->iconColor('success')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('program.name')
                    ->label('Program')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->wrap(),
                TextColumn::make('coordinates')
                    ->label('Koordinat')
                    ->state(fn (Site $record): string => self::coordinates($record))
                    ->placeholder('Belum diisi')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->iconColor('primary')
                    ->badge()
                    ->fontFamily('mono')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->placeholder('Semua Program')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->searchPlaceholder('Cari lokasi...')
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->authorize('view')
                    ->schema(SiteForm::components()),
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->authorize('update')
                    ->modalHeading('Ubah lokasi tanam')
                    ->modalSubmitActionLabel('Simpan perubahan')
                    ->schema(SiteForm::components(includeReason: true))
                    ->fillForm(fn (Site $record): array => [
                        'program_id' => $record->program_id,
                        'name' => $record->name,
                        'address' => $record->address,
                        'latitude' => $record->latitude,
                        'longitude' => $record->longitude,
                    ])
                    ->action(function (Site $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(UpdateSite::class)->handle($actor, $record, $data);
                    })
                    ->successNotificationTitle('Lokasi berhasil diperbarui.'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->authorize('delete')
                    ->modalHeading('Hapus lokasi tanam')
                    ->modalDescription('Lokasi hanya dapat dihapus jika belum dipakai petak tanam atau kegiatan.')
                    ->modalSubmitActionLabel('Hapus lokasi')
                    ->successNotificationTitle('Lokasi berhasil dihapus.')
                    ->disabled(fn (Site $record): bool => $record->plots_count > 0 || $record->activities_count > 0)
                    ->tooltip(fn (Site $record): ?string => self::deleteTooltip($record))
                    ->action(function (Site $record): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(DeleteUnusedSite::class)->handle($actor, $record);
                    }),
            ])
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([20, 50])
            ->emptyStateHeading('Belum ada lokasi tanam')
            ->emptyStateDescription('Tambahkan lokasi untuk menghubungkannya dengan program penghijauan.')
            ->emptyStateIcon('heroicon-o-map-pin')
            ->defaultSort('name');
    }

    private static function coordinates(Site $site): string
    {
        if ($site->latitude === null && $site->longitude === null) {
            return 'Belum diisi';
        }

        $latitude = $site->latitude === null ? 'Belum diisi' : number_format((float) $site->latitude, 6, '.', '');
        $longitude = $site->longitude === null ? 'Belum diisi' : number_format((float) $site->longitude, 6, '.', '');

        return $latitude.', '.$longitude;
    }

    private static function deleteTooltip(Site $site): ?string
    {
        if ($site->plots_count > 0) {
            return 'Lokasi sudah dipakai petak tanam.';
        }

        if ($site->activities_count > 0) {
            return 'Lokasi sudah dipakai kegiatan.';
        }

        return null;
    }
}
