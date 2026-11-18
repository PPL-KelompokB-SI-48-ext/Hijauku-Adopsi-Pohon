<?php

namespace App\Filament\FieldStaff\Resources\CareRecords;

use App\Filament\FieldStaff\Resources\CareRecords\Pages\CreateCareRecord;
use App\Filament\FieldStaff\Resources\CareRecords\Pages\ListCareRecords;
use App\Filament\FieldStaff\Resources\CareRecords\Schemas\CareRecordForm;
use App\Filament\FieldStaff\Resources\CareRecords\Tables\CareRecordsTable;
use App\Models\CareRecord;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'realisasi perawatan';

    protected static ?string $pluralModelLabel = 'realisasi perawatan';

    public static function form(Schema $schema): Schema
    {
        return CareRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CareRecordsTable::configure($table);
    }

    /**
     * Restrict the list query to records owned by the authenticated field staff.
     * Policy alone is not sufficient; query scope prevents data leaks.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->with(['tree.species', 'tree.plot.site', 'plan']);

        if ($user instanceof User && $user->isFieldStaff()) {
            $query->forPerformer((int) $user->getKey());
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCareRecords::route('/'),
            'create' => CreateCareRecord::route('/create'),
        ];
    }
}
