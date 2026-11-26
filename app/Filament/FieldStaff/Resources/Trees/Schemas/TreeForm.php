<?php

namespace App\Filament\FieldStaff\Resources\Trees\Schemas;

use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\TreeSpecies;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class TreeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('species_id')
                    ->label('Jenis pohon')
                    ->required()
                    ->options(fn () => TreeSpecies::active()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->rules([
                        Rule::exists('tree_species', 'id')->where('is_active', true),
                    ])
                    ->validationMessages([
                        'required' => 'Jenis pohon wajib dipilih.',
                        'exists' => 'Jenis pohon yang dipilih tidak aktif atau tidak valid.',
                    ])
                    ->columnSpanFull(),

                Select::make('plot_id')
                    ->label('Petak tanam')
                    ->relationship(
                        'plot',
                        'code',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->where('plots.status', 'open')
                            ->whereRaw('plots.capacity > (SELECT COUNT(*) FROM trees WHERE trees.plot_id = plots.id)')
                            ->withCount('trees')
                            ->orderBy('plots.code'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Plot $record): string => "{$record->code} ({$record->trees_count}/{$record->capacity})")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('batch_id', null))
                    ->helperText('Petak yang sudah penuh tidak tersedia. Kapasitas tetap diperiksa kembali saat pohon disimpan.')
                    ->validationMessages([
                        'required' => 'Petak tanam wajib dipilih.',
                    ])
                    ->columnSpanFull(),

                Select::make('batch_id')
                    ->label('Kelompok penanaman (opsional)')
                    ->options(function ($get) {
                        $plotId = $get('plot_id');
                        if (! $plotId) {
                            return [];
                        }

                        return PlantingBatch::query()
                            ->where('plot_id', $plotId)
                            ->pluck('code', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->rules([
                        fn ($get) => Rule::exists('planting_batches', 'id')
                            ->where('plot_id', $get('plot_id')),
                    ])
                    ->validationMessages([
                        'exists' => 'Kelompok penanaman yang dipilih harus berada pada petak yang sama.',
                    ])
                    ->helperText('Pohon hanya dapat dikelompokkan pada kelompok penanaman di petak yang sama.')
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label('Kode pohon')
                    ->required()
                    ->maxLength(60)
                    ->unique(Tree::class, 'code', ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'Kode pohon wajib diisi.',
                        'unique' => 'Kode pohon sudah terdaftar.',
                    ])
                    ->columnSpanFull(),

                DatePicker::make('planted_on')
                    ->label('Tanggal tanam')
                    ->nullable()
                    ->columnSpanFull(),

                TextInput::make('initial_condition')
                    ->label('Kondisi awal')
                    ->required()
                    ->maxLength(32)
                    ->default('baik')
                    ->validationMessages([
                        'required' => 'Kondisi awal wajib diisi.',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
