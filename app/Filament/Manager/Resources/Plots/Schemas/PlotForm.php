<?php

namespace App\Filament\Manager\Resources\Plots\Schemas;

use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class PlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components())
            ->columns(2);
    }

    /**
     * @return array<int, Component>
     */
    public static function components(): array
    {
        return [
            Select::make('site_id')
                ->label('Lokasi')
                ->relationship(
                    name: 'site',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query
                        ->with('program')
                        ->orderBy('name'),
                )
                ->getOptionLabelFromRecordUsing(fn (Site $record): string => $record->name.' - '.($record->program?->name ?? 'Tanpa program'))
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->validationMessages([
                    'required' => 'Lokasi petak wajib dipilih.',
                    'exists' => 'Lokasi yang dipilih tidak tersedia.',
                ]),
            TextInput::make('code')
                ->label('Kode petak')
                ->required()
                ->maxLength(40)
                ->placeholder('Contoh: A-01')
                ->helperText('Kode harus unik di dalam lokasi yang dipilih.')
                ->rules([
                    fn ($get) => Rule::unique('plots', 'code')->where(
                        fn ($query) => $query->where('site_id', $get('site_id')),
                    ),
                ])
                ->validationMessages([
                    'required' => 'Kode petak wajib diisi.',
                    'max' => 'Kode petak maksimal 40 karakter.',
                    'unique' => 'Kode petak sudah digunakan pada lokasi ini.',
                ]),
            TextInput::make('capacity')
                ->label('Kapasitas')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->maxValue(65535)
                ->step(1)
                ->suffix('Pohon')
                ->required()
                ->helperText('Masukkan jumlah maksimum pohon sebagai bilangan bulat positif.')
                ->validationMessages([
                    'required' => 'Kapasitas petak wajib diisi.',
                    'integer' => 'Kapasitas harus berupa bilangan bulat.',
                    'min' => 'Kapasitas petak minimal 1 pohon.',
                    'max' => 'Kapasitas petak maksimal 65.535 pohon.',
                ]),
        ];
    }
}
