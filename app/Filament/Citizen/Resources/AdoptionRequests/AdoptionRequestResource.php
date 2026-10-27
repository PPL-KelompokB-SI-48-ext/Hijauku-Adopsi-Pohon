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
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdoptionRequests::route('/'),
            'create' => CreateAdoptionRequest::route('/create'),
        ];
    }
}