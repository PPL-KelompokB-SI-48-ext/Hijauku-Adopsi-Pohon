<?php

namespace App\Filament\FieldStaff\Resources\Trees;

use App\Filament\FieldStaff\Resources\Trees\Pages\CreateTree;
use App\Filament\FieldStaff\Resources\Trees\Pages\ListTrees;
use App\Filament\FieldStaff\Resources\Trees\Schemas\TreeForm;
use App\Filament\FieldStaff\Resources\Trees\Tables\TreesTable;
use App\Models\Tree;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TreeResource extends Resource
{
    protected static ?string $model = Tree::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Inventaris Pohon';

    protected static string|UnitEnum|null $navigationGroup = 'Inventarisasi Lapangan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'pohon';

    protected static ?string $pluralModelLabel = 'inventaris pohon';

    public static function form(Schema $schema): Schema
    {
        return TreeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['species', 'plot']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrees::route('/'),
            'create' => CreateTree::route('/create'),
        ];
    }
}
