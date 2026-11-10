<?php

namespace App\Filament\Manager\Resources\Sites;

use App\Filament\Manager\Resources\Sites\Pages\CreateSite;
use App\Filament\Manager\Resources\Sites\Pages\ListSites;
use App\Filament\Manager\Resources\Sites\Schemas\SiteForm;
use App\Filament\Manager\Resources\Sites\Tables\SitesTable;
use App\Models\Site;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Lokasi Tanam';

    protected static string|UnitEnum|null $navigationGroup = 'Program & Lokasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'lokasi tanam';

    protected static ?string $pluralModelLabel = 'lokasi tanam';

    public static function form(Schema $schema): Schema
    {
        return SiteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SitesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('program')
            ->withCount(['plots', 'activities']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSites::route('/'),
            'create' => CreateSite::route('/create'),
        ];
    }
}
