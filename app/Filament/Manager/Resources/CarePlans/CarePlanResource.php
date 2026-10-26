<?php

namespace App\Filament\Manager\Resources\CarePlans;

use App\Filament\Manager\Resources\CarePlans\Pages\CreateCarePlan;
use App\Filament\Manager\Resources\CarePlans\Pages\ListCarePlans;
use App\Filament\Manager\Resources\CarePlans\Schemas\CarePlanForm;
use App\Filament\Manager\Resources\CarePlans\Tables\CarePlansTable;
use App\Models\CarePlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CarePlanResource extends Resource
{
    protected static ?string $model = CarePlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Rencana Perawatan';

    protected static string|UnitEnum|null $navigationGroup = 'Perawatan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'rencana perawatan';

    protected static ?string $pluralModelLabel = 'rencana perawatan';

    public static function form(Schema $schema): Schema
    {
        return CarePlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarePlansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['tree.species', 'tree.plot', 'assignment.assignee']);
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
            'index' => ListCarePlans::route('/'),
            'create' => CreateCarePlan::route('/create'),
        ];
    }
}
