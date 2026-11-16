<?php

namespace App\Filament\Manager\Resources\CareRecords;

use App\Filament\Manager\Resources\CareRecords\Pages\ListCareRecords;
use App\Filament\Manager\Resources\CareRecords\Tables\CareRecordsTable;
use App\Models\CareRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CareRecordResource extends Resource
{
    protected static ?string $model = CareRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Realisasi Perawatan';

    protected static string|UnitEnum|null $navigationGroup = 'Perawatan';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'realisasi perawatan';

    protected static ?string $pluralModelLabel = 'realisasi perawatan';

    public static function canCreate(): bool
    {
        // Realisasi perawatan hanya dicatat oleh petugas lapangan di panel /petugas
        return false;
    }

    public static function table(Table $table): Table
    {
        return CareRecordsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'tree.species',
            'tree.plot.site',
            'performer',
            'verifier',
            'plan',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCareRecords::route('/'),
        ];
    }
}
