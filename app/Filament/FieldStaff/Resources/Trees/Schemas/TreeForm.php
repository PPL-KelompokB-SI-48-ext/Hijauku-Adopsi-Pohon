<?php

namespace App\Filament\FieldStaff\Resources\Trees\Schemas;

use App\Models\Tree;
use App\Models\TreeSpecies;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                        \Illuminate\Validation\Rule::exists('tree_species', 'id')->where('is_active', true),
                    ])
                    ->validationMessages([
                        'required' => 'Jenis pohon wajib dipilih.',
                        'exists' => 'Jenis pohon yang dipilih tidak aktif atau tidak valid.',
                    ])
                    ->columnSpanFull(),

                Select::make('plot_id')
                    ->label('Petak tanam')
                    ->relationship('plot', 'code')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Petak tanam wajib dipilih.',
                    ])
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
