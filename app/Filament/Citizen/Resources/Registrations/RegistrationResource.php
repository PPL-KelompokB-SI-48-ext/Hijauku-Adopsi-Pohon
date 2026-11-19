<?php

namespace App\Filament\Citizen\Resources\Registrations;

use App\Filament\Citizen\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Citizen\Resources\Registrations\Tables\RegistrationsTable;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Pendaftaran Saya';

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'pendaftaran';

    protected static ?string $pluralModelLabel = 'pendaftaran saya';

    public static function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        // Citizen hanya melihat pendaftaran milik dirinya sendiri (query ownership)
        $userId = auth()->id();

        return parent::getEloquentQuery()
            ->with(['activity.site'])
            ->where('user_id', $userId);
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
