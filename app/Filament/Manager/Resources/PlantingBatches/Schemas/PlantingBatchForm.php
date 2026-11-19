<?php

namespace App\Filament\Manager\Resources\PlantingBatches\Schemas;

use App\Models\PlantingBatch;
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
                    ->disabled(fn (?PlantingBatch $record) => $record && $record->trees()->exists())
                    ->helperText(fn (?PlantingBatch $record) => $record && $record->trees()->exists() ? 'Petak tidak dapat diubah karena kelompok sudah memiliki pohon terdaftar.' : null)
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
                        fn ($get, ?PlantingBatch $record) => Rule::unique('planting_batches', 'code')
                            ->where(function ($query) use ($get, $record) {
                                $plotId = $get('plot_id') ?? $record?->plot_id;

                                return $query->where('plot_id', $plotId);
                            })
                            ->ignore($record?->getKey()),
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

                Textarea::make('reason')
                    ->label('Alasan Koreksi')
                    ->required()
                    ->minLength(3)
                    ->maxLength(1000)
                    ->placeholder('Contoh: Penyesuaian tanggal tanam aktual dan perbaikan catatan petak.')
                    ->helperText('Alasan koreksi data kelompok wajib diisi untuk pencatatan riwayat audit.')
                    ->validationMessages([
                        'required' => 'Alasan koreksi wajib diisi.',
                        'min' => 'Alasan koreksi minimal 3 karakter.',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

