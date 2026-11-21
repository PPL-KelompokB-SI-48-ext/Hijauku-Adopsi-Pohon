<?php

namespace App\Filament\Manager\Resources\AdoptionAgreements\Schemas;

use App\Models\AdoptionRequest;
use App\Models\TreeAdoptionAllocation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AdoptionAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('request_id')
                    ->label('Permohonan Adopsi')
                    ->options(fn (): array => AdoptionRequest::query()
                        ->with(['tree.species', 'requester'])
                        ->where('status', 'approved')
                        ->whereDoesntHave('agreement')
                        ->whereDoesntHave(
                            'tree.adoptionAllocations',
                            fn ($query) => $query->whereIn(
                                'status',
                                TreeAdoptionAllocation::VALID_STATUSES
                            )
                        )
                        ->get()
                        ->mapWithKeys(fn (AdoptionRequest $request): array => [
                            $request->id => $request->tree->code
                                .' - '.$request->tree->species->name
                                .' | '.$request->requester->name,
                        ])
                        ->all())
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Hanya permohonan yang disetujui, belum memiliki kesepakatan, dan pohonnya belum direservasi yang dapat dipilih.'),

                DatePicker::make('starts_on')
                    ->label('Tanggal Mulai Adopsi')
                    ->required()
                    ->native(false),

                DatePicker::make('ends_on')
                    ->label('Tanggal Selesai Adopsi')
                    ->required()
                    ->after('starts_on')
                    ->native(false)
                    ->validationMessages([
                        'after' => 'Tanggal selesai harus setelah tanggal mulai.',
                    ]),

                Textarea::make('terms')
                    ->label('Syarat dan Komitmen Adopsi')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull()
                    ->helperText('Tuliskan komitmen dan ketentuan yang harus dipenuhi oleh pengadopsi.'),
            ])
            ->columns(2);
    }
}
