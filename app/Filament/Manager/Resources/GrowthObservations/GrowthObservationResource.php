<?php

namespace App\Filament\Manager\Resources\GrowthObservations;

use App\Filament\Manager\Resources\GrowthObservations\Pages\ListGrowthObservations;
use App\Filament\Manager\Resources\GrowthObservations\Tables\GrowthObservationsTable;
use App\Models\GrowthObservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GrowthObservationResource extends Resource
{
    protected static ?string $model = GrowthObservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?string $navigationLabel = 'Pengamatan Pertumbuhan';

    protected static string|UnitEnum|null $navigationGroup = 'Perawatan';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'pengamatan pertumbuhan';

    protected static ?string $pluralModelLabel = 'pengamatan pertumbuhan';

    public static function canCreate(): bool
    {
        // Pengamatan pertumbuhan hanya dicatat oleh petugas lapangan di panel /petugas.
        return false;
    }

    public static function table(Table $table): Table
    {
        return GrowthObservationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'tree.species',
            'tree.plot.site',
            'observer',
            'verifier',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGrowthObservations::route('/'),
        ];
    }
}
