<?php

namespace App\Filament\Manager\Resources\FieldAssignments;

use App\Filament\Manager\Resources\FieldAssignments\Pages\CreateFieldAssignment;
use App\Filament\Manager\Resources\FieldAssignments\Pages\ListFieldAssignments;
use App\Filament\Manager\Resources\FieldAssignments\Schemas\FieldAssignmentForm;
use App\Filament\Manager\Resources\FieldAssignments\Tables\FieldAssignmentsTable;
use App\Models\FieldAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FieldAssignmentResource extends Resource
{
    protected static ?string $model = FieldAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Penugasan Lapangan';

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'penugasan lapangan';

    protected static ?string $pluralModelLabel = 'penugasan lapangan';

    public static function form(Schema $schema): Schema
    {
        return FieldAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FieldAssignmentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['activity.site', 'tree.species', 'assignee']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFieldAssignments::route('/'),
            'create' => CreateFieldAssignment::route('/create'),
        ];
    }
}
