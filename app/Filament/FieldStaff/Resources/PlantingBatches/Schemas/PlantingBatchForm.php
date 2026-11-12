<?php

namespace App\Filament\FieldStaff\Resources\PlantingBatches\Schemas;

use App\Models\PlantingBatch;
use App\Models\Plot;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PlantingBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('plot_id')
                    ->label('Petak Tanam')
                    ->relationship('plot', 'code')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->validationMessages([
                        'required' => 'Petak tanam wajib dipilih.',
                    ])
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label('Kode Kelompok Penanaman')
                    ->required()
                    ->maxLength(40)
                    ->placeholder('Contoh: BATCH-A01')
                    ->rules([
                        fn ($get) => Rule::unique('planting_batches', 'code')->where(function ($query) use ($get) {
                            return $query->where('plot_id', $get('plot_id'));
                        }),
                    ])
                    ->validationMessages([
                        'required' => 'Kode kelompok penanaman wajib diisi.',
                        'max' => 'Kode kelompok penanaman maksimal 40 karakter.',
                        'unique' => 'Kode kelompok penanaman sudah digunakan pada petak ini.',
                    ])
                    ->columnSpanFull(),

                DatePicker::make('planted_on')
                    ->label('Tanggal Pelaksanaan Penanaman')
                    ->required()
                    ->default(now()->toDateString())
                    ->maxDate(now())
                    ->rules(['before_or_equal:today'])
                    ->helperText('Tanggal pelaksanaan penanaman tidak boleh di masa depan.')
                    ->validationMessages([
                        'required' => 'Tanggal pelaksanaan penanaman wajib diisi.',
                        'before_or_equal' => 'Tanggal pelaksanaan penanaman tidak boleh di masa depan.',
                    ])
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Catatan Pelaksanaan')
                    ->rows(3)
                    ->placeholder('Catatan kondisi lapangan atau pelaksanaan penanaman (opsional).')
                    ->columnSpanFull(),
            ]);
    }
}
