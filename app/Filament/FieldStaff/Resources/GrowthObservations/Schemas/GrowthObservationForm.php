<?php

namespace App\Filament\FieldStaff\Resources\GrowthObservations\Schemas;

use App\Models\GrowthObservation;
use App\Models\Tree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GrowthObservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Pilih pohon yang diamati...')
                    ->options(fn (): array => Tree::query()
                        ->with(['species', 'plot'])
                        ->orderBy('code')
                        ->get()
                        ->mapWithKeys(function (Tree $tree): array {
                            $label = $tree->code;
                            if ($tree->species) {
                                $label .= ' - '.$tree->species->name;
                            }
                            if ($tree->plot) {
                                $label .= ' (Petak '.$tree->plot->code.')';
                            }

                            return [$tree->id => $label];
                        })
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->validationMessages([
                        'required' => 'Pohon wajib dipilih.',
                    ])
                    ->helperText('Pilih pohon dari inventaris untuk dicatat pengamatan terukurnya.'),

                DatePicker::make('observed_on')
                    ->label('Tanggal Pengamatan')
                    ->required()
                    ->native(false)
                    ->maxDate(now())
                    ->default(now()->toDateString())
                    ->validationMessages([
                        'required' => 'Tanggal pengamatan wajib diisi.',
                    ])
                    ->helperText('Maksimal satu pengamatan untuk setiap pohon per tanggal.'),

                TextInput::make('height_cm')
                    ->label('Tinggi Pohon (cm)')
                    ->placeholder('Contoh: 185.50')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->maxValue(999999.99)
                    ->suffix('cm')
                    ->required()
                    ->rules(['numeric', 'gt:0', 'max:999999.99'])
                    ->validationMessages([
                        'required' => 'Tinggi pohon wajib diisi.',
                        'numeric' => 'Tinggi pohon harus berupa angka.',
                        'gt' => 'Tinggi pohon harus berupa bilangan positif lebih dari 0.',
                        'min' => 'Tinggi pohon harus berupa bilangan positif lebih dari 0.',
                    ])
                    ->columnSpan(1),

                TextInput::make('diameter_cm')
                    ->label('Diameter Batang (cm)')
                    ->placeholder('Contoh: 12.40')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->maxValue(999999.99)
                    ->suffix('cm')
                    ->required()
                    ->rules(['numeric', 'gt:0', 'max:999999.99'])
                    ->validationMessages([
                        'required' => 'Diameter batang wajib diisi.',
                        'numeric' => 'Diameter batang harus berupa angka.',
                        'gt' => 'Diameter batang harus berupa bilangan positif lebih dari 0.',
                        'min' => 'Diameter batang harus berupa bilangan positif lebih dari 0.',
                    ])
                    ->columnSpan(1),

                Select::make('condition')
                    ->label('Kondisi Pohon')
                    ->placeholder('Pilih kondisi fisik pohon...')
                    ->options(GrowthObservation::conditionOptions())
                    ->native(false)
                    ->required()
                    ->validationMessages([
                        'required' => 'Kondisi pohon wajib dipilih.',
                    ])
                    ->columnSpan(1),

                FileUpload::make('photo')
                    ->label('Bukti Foto (Opsional)')
                    ->image()
                    ->disk('private')
                    ->directory('growth-observations/photos')
                    ->visibility('private')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->validationMessages([
                        'max' => 'Ukuran foto maksimal 5 MB.',
                    ])
                    ->columnSpan(1)
                    ->helperText('Opsional. Format JPG, PNG, atau WebP, maksimal 5 MB.'),
            ])
            ->columns(2);
    }
}
