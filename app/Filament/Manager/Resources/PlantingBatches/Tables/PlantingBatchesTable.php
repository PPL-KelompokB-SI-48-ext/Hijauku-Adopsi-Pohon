<?php

namespace App\Filament\Manager\Resources\PlantingBatches\Tables;

use App\Actions\PlantingBatches\DeletePlantingBatch;
use App\Models\PlantingBatch;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class PlantingBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Kelompok')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plot.code')
                    ->label('Petak Tanam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('planted_on')
                    ->label('Tanggal Tanam')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('trees_count')
                    ->label('Pohon')
                    ->counts('trees')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(60)
                    ->wrap()
                    ->default('-'),

                TextColumn::make('created_at')
                    ->label('Dicatat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                // 1. Daftar pohon per kelompok
                Action::make('viewTrees')
                    ->label('Daftar Pohon')
                    ->icon(Heroicon::OutlinedEye)
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs')
                    ->modalHeading(fn (PlantingBatch $record) => "Daftar Pohon: {$record->code} (Petak {$record->plot?->code})")
                    ->modalDescription('Pohon-pohon yang terdaftar pada pelaksanaan penanaman ini.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->form([
                        Placeholder::make('trees_list')
                            ->label('Pohon Terdaftar')
                            ->content(function (PlantingBatch $record): HtmlString {
                                $trees = $record->trees()->with('species')->get();

                                if ($trees->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Belum ada pohon yang dikelompokkan ke dalam batch ini.</p>');
                                }

                                $rows = '';
                                foreach ($trees as $tree) {
                                    $speciesName = e($tree->species?->name ?? '-');
                                    $code = e($tree->code);
                                    $condition = e(ucfirst($tree->initial_condition ?? '-'));
                                    $date = $tree->planted_on ? e($tree->planted_on->format('d/m/Y')) : '-';

                                    $rows .= "<tr class='border-b dark:border-gray-700'>
                                        <td class='py-2 px-3 font-medium'>{$code}</td>
                                        <td class='py-2 px-3'>{$speciesName}</td>
                                        <td class='py-2 px-3'>{$condition}</td>
                                        <td class='py-2 px-3'>{$date}</td>
                                    </tr>";
                                }

                                return new HtmlString("
                                    <div class='overflow-x-auto'>
                                        <table class='min-w-full text-sm text-left'>
                                            <thead class='bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300'>
                                                <tr>
                                                    <th class='py-2 px-3'>Kode Pohon</th>
                                                    <th class='py-2 px-3'>Jenis Pohon</th>
                                                    <th class='py-2 px-3'>Kondisi</th>
                                                    <th class='py-2 px-3'>Tanggal Tanam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$rows}
                                            </tbody>
                                        </table>
                                    </div>
                                ");
                            }),
                    ]),

                // 2. Edit kelompok dengan alasan
                EditAction::make()
                    ->label('Edit')
                    ->button()
                    ->outlined()
                    ->color('gray')
                    ->size('xs'),

                // 3. Hapus kelompok (hanya tampil jika belum ada pohon terdaftar)
                DeleteAction::make()
                    ->label('Hapus')
                    ->button()
                    ->outlined()
                    ->color('danger')
                    ->size('xs')
                    ->visible(fn (PlantingBatch $record): bool => (int) $record->trees_count === 0)
                    ->modalHeading('Hapus Kelompok Penanaman')
                    ->modalDescription(fn (PlantingBatch $record): string => "Hapus kelompok \"{$record->code}\" secara permanen? Tindakan ini tidak dapat dibatalkan.")
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->using(function (PlantingBatch $record): bool {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        try {
                            app(DeletePlantingBatch::class)->handle($actor, $record);

                            return true;
                        } catch (ValidationException $e) {
                            return false;
                        }
                    })
                    ->successNotificationTitle('Kelompok penanaman berhasil dihapus.'),
            ]);
    }
}
