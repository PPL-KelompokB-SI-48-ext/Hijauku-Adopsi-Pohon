<?php

namespace App\Filament\FieldStaff\Resources\PlantingBatches\Tables;

use App\Actions\PlantingBatches\AssignTreesToPlantingBatch;
use App\Models\PlantingBatch;
use App\Models\Tree;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                // AC 3: Daftar pohon per kelompok bisa dibuka
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
                                                    <th class='py-2 px-3'>Kondisi Awal</th>
                                                    <th class='py-2 px-3'>Tanggal Tanam</th>
                                                </tr>
                                            </thead>
                                            <tbody>{$rows}</tbody>
                                        </table>
                                    </div>
                                ");
                            }),
                    ]),

                // AC 1 & 2: Tambahkan pohon dalam kelompok (satu petak & satu kelompok)
                Action::make('assignTrees')
                    ->label('Tambah Pohon')
                    ->icon(Heroicon::OutlinedPlus)
                    ->button()
                    ->outlined()
                    ->color('primary')
                    ->size('xs')
                    ->modalHeading(fn (PlantingBatch $record) => "Tambah Pohon ke {$record->code}")
                    ->modalDescription(fn (PlantingBatch $record) => "Pilih pohon pada Petak {$record->plot?->code} untuk dikelompokkan ke dalam batch ini.")
                    ->form([
                        Select::make('tree_ids')
                            ->label('Pilih Pohon')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (PlantingBatch $record) {
                                return Tree::query()
                                    ->where('plot_id', $record->plot_id)
                                    ->where(function ($q) use ($record) {
                                        $q->whereNull('batch_id')
                                          ->orWhere('batch_id', '!=', $record->getKey());
                                    })
                                    ->get()
                                    ->mapWithKeys(function (Tree $tree) {
                                        $batchInfo = $tree->batch_id ? ' (saat ini di batch lain)' : '';
                                        return [$tree->getKey() => "{$tree->code} - {$tree->species?->name}{$batchInfo}"];
                                    });
                            })
                            ->helperText('Hanya pohon pada petak yang sama yang ditampilkan.'),
                    ])
                    ->action(function (PlantingBatch $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(AssignTreesToPlantingBatch::class)->handle(
                            $actor,
                            $record,
                            array_map('intval', (array) ($data['tree_ids'] ?? []))
                        );
                    })
                    ->successNotificationTitle('Pohon berhasil ditambahkan ke kelompok penanaman.'),
            ])
            ->emptyStateHeading('Belum ada kelompok penanaman')
            ->emptyStateDescription('Catat pelaksanaan penanaman baru pada petak tanam.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->defaultSort('planted_on', 'desc');
    }
}
