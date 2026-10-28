<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests;

use App\Filament\Citizen\Resources\AdoptionRequests\Pages\CreateAdoptionRequest;
use App\Filament\Citizen\Resources\AdoptionRequests\Pages\ListAdoptionRequests;
use App\Filament\Citizen\Resources\AdoptionRequests\Schemas\AdoptionRequestForm;
use App\Models\AdoptionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AdoptionRequestResource extends Resource
{
    protected static ?string $model = AdoptionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Permohonan Adopsi';

    protected static ?string $modelLabel = 'Permohonan Adopsi';

    protected static ?string $pluralModelLabel = 'Permohonan Adopsi';

    public static function form(Schema $schema): Schema
    {
        return AdoptionRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query->where('requester_id', auth()->id())
            )
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Kode Pohon')
                    ->searchable(),

                TextColumn::make('tree.species.name')
                    ->label('Jenis Pohon')
                    ->searchable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdoptionRequests::route('/'),
            'create' => CreateAdoptionRequest::route('/create'),
        ];
    }
}