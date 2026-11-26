<?php

namespace App\Filament\Manager\Resources\Plots;

use App\Filament\Manager\Resources\Plots\Pages\CreatePlot as CreatePlotPage;
use App\Filament\Manager\Resources\Plots\Pages\ListPlots;
use App\Filament\Manager\Resources\Plots\Schemas\PlotForm;
use App\Filament\Manager\Resources\Plots\Tables\PlotsTable;
use App\Models\Plot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PlotResource extends Resource
{
    protected static ?string $model = Plot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $navigationLabel = 'Petak Tanam';

    protected static string|UnitEnum|null $navigationGroup = 'Program & Lokasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'petak tanam';

    protected static ?string $pluralModelLabel = 'petak tanam';

    public static function form(Schema $schema): Schema
    {
        return PlotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlotsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['site.program'])
            ->withCount('trees');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlots::route('/'),
            'create' => CreatePlotPage::route('/create'),
        ];
    }
}
