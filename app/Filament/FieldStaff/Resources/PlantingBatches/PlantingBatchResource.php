<?php

namespace App\Filament\FieldStaff\Resources\PlantingBatches;

use App\Filament\FieldStaff\Resources\PlantingBatches\Pages\CreatePlantingBatch;
use App\Filament\FieldStaff\Resources\PlantingBatches\Pages\ListPlantingBatches;
use App\Filament\FieldStaff\Resources\PlantingBatches\Schemas\PlantingBatchForm;
use App\Filament\FieldStaff\Resources\PlantingBatches\Tables\PlantingBatchesTable;
use App\Models\PlantingBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PlantingBatchResource extends Resource
{
    protected static ?string $model = PlantingBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Kelompok Penanaman';

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan Lapangan';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'kelompok penanaman';

    protected static ?string $pluralModelLabel = 'kelompok penanaman';

    public static function form(Schema $schema): Schema
    {
        return PlantingBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlantingBatchesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['plot'])
            ->withCount('trees');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlantingBatches::route('/'),
            'create' => CreatePlantingBatch::route('/create'),
        ];
    }
}

