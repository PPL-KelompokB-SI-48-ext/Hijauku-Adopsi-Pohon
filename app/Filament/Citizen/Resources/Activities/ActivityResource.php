<?php

namespace App\Filament\Citizen\Resources\Activities;

use App\Filament\Citizen\Resources\Activities\Pages\ListActivities;
use App\Filament\Citizen\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Kegiatan Tersedia';

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'kegiatan';

    protected static ?string $pluralModelLabel = 'kegiatan tersedia';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isCitizen() ?? false;
    }

    public static function can(UnitEnum|string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        $actionName = $action instanceof UnitEnum ? $action->name : (string) $action;
        $user = auth()->user();
        if (! $user || ! $user->isCitizen()) {
            return false;
        }

        if (in_array($actionName, ['create', 'edit', 'delete', 'forceDelete', 'restore'], true)) {
            return false;
        }

        return true;
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        // Citizen hanya melihat kegiatan yang berstatus 'open' (tersedia untuk didaftari)
        return parent::getEloquentQuery()
            ->with(['site', 'registrations'])
            ->where('status', 'open');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
