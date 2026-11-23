<?php

namespace App\Filament\FieldStaff\Resources\GrowthObservations;

use App\Filament\FieldStaff\Resources\GrowthObservations\Pages\CreateGrowthObservation;
use App\Filament\FieldStaff\Resources\GrowthObservations\Pages\ListGrowthObservations;
use App\Filament\FieldStaff\Resources\GrowthObservations\Schemas\GrowthObservationForm;
use App\Filament\FieldStaff\Resources\GrowthObservations\Tables\GrowthObservationsTable;
use App\Models\GrowthObservation;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'pengamatan pertumbuhan';

    protected static ?string $pluralModelLabel = 'pengamatan pertumbuhan';

    public static function form(Schema $schema): Schema
    {
        return GrowthObservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GrowthObservationsTable::configure($table);
    }

    /**
     * Batasi daftar pengamatan agar petugas lapangan hanya melihat pengamatan miliknya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->with(['tree.species', 'tree.plot.site', 'verifier']);

        if ($user instanceof User && $user->isFieldStaff()) {
            $query->forObserver((int) $user->getKey());
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGrowthObservations::route('/'),
            'create' => CreateGrowthObservation::route('/create'),
        ];
    }
}
