<?php

namespace App\Filament\Manager\Resources\Registrations;

use App\Filament\Manager\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Manager\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\ActivityRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RegistrationResource extends Resource
{
    protected static ?string $model = ActivityRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Kehadiran Peserta';

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'kehadiran peserta';

    protected static ?string $pluralModelLabel = 'kehadiran peserta';

    public static function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['activity.site', 'user']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrations::route('/'),
        ];
    }
}
