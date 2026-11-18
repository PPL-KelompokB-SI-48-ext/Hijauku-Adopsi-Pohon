<?php

namespace App\Filament\Manager\Resources\PlantingBatches\Schemas;

use App\Models\PlantingBatch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlantingBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('plot_id')
                    ->label('Petak tanam')
                    ->relationship('plot', 'code')
                    ->required()
                    ->disabled(fn (?PlantingBatch $record) => (bool) ($record && $record->trees()->exists()))
                    ->helperText(fn (?PlantingBatch $record) => $record && $record->trees()->exists()
                        ? 'Petak tidak dapat diubah karena kelompok sudah memiliki pohon terdaftar.'
                        : null)
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label('Kode kelompok')
                    ->required()
                    ->maxLength(40)
                    ->placeholder('Contoh: BATCH-001')
                    ->columnSpanFull(),

                DatePicker::make('planted_on')
                    ->label('Tanggal tanam')
                    ->required()
                    ->maxDate(now())
                    ->default(now())
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->maxLength(5000)
                    ->placeholder('Catatan pelaksanaan atau informasi penting lainnya.')
                    ->columnSpanFull(),

                Textarea::make('reason')
                    ->label('Alasan perubahan')
                    ->required()
                    ->minLength(3)
                    ->maxLength(1000)
                    ->rows(3)
                    ->helperText('Alasan koreksi kelompok penanaman wajib dicatat untuk audit trail.')
                    ->validationMessages([
                        'required' => 'Alasan perubahan wajib diisi.',
                        'min' => 'Alasan perubahan minimal 3 karakter.',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
