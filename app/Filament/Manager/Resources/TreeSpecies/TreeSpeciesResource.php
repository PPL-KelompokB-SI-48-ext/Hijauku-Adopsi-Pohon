<?php

namespace App\Filament\Manager\Resources\TreeSpecies;

use App\Filament\Manager\Resources\TreeSpecies\Pages\CreateTreeSpecies;
use App\Filament\Manager\Resources\TreeSpecies\Pages\ListTreeSpecies;
use App\Filament\Manager\Resources\TreeSpecies\Schemas\TreeSpeciesForm;
use App\Filament\Manager\Resources\TreeSpecies\Tables\TreeSpeciesTable;
use App\Models\TreeSpecies;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TreeSpeciesResource extends Resource
{
    protected static ?string $model = TreeSpecies::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Jenis Pohon';

    protected static string|UnitEnum|null $navigationGroup = 'Data Dasar Pohon';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'jenis pohon';

    protected static ?string $pluralModelLabel = 'jenis pohon';

    public static function form(Schema $schema): Schema
    {
        return TreeSpeciesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreeSpeciesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('trees');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreeSpecies::route('/'),
            'create' => CreateTreeSpecies::route('/create'),
        ];
    }
}
