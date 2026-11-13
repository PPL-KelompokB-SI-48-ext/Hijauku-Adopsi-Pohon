<?php

namespace App\Filament\Manager\Resources\AdoptionRequests;

use App\Filament\Manager\Resources\AdoptionRequests\Pages\ListAdoptionRequests;
use App\Filament\Manager\Resources\AdoptionRequests\Tables\AdoptionRequestsTable;
use App\Models\AdoptionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AdoptionRequestResource extends Resource
{
    protected static ?string $model = AdoptionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Permohonan Adopsi';

    protected static string|UnitEnum|null $navigationGroup = 'Adopsi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'permohonan adopsi';

    protected static ?string $pluralModelLabel = 'permohonan adopsi';

    public static function table(Table $table): Table
    {
        return AdoptionRequestsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tree.species', 'tree.plot', 'requester']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdoptionRequests::route('/'),
        ];
    }
}
