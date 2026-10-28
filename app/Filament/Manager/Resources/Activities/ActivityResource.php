<?php

namespace App\Filament\Manager\Resources\Activities;

use App\Filament\Manager\Resources\Activities\Pages\CreateActivity;
use App\Filament\Manager\Resources\Activities\Pages\ListActivities;
use App\Filament\Manager\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Manager\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Kegiatan Lapangan';

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'kegiatan lapangan';

    protected static ?string $pluralModelLabel = 'kegiatan lapangan';

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('site');
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
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
        ];
    }
}

