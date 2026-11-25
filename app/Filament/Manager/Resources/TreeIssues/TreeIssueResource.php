<?php

namespace App\Filament\Manager\Resources\TreeIssues;

use App\Filament\Manager\Resources\TreeIssues\Pages\ListTreeIssues;
use App\Filament\Manager\Resources\TreeIssues\Tables\TreeIssuesTable;
use App\Models\TreeIssue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TreeIssueResource extends Resource
{
    protected static ?string $model = TreeIssue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Laporan Masalah Pohon';

    protected static string|UnitEnum|null $navigationGroup = 'Adopsi';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'laporan masalah';

    protected static ?string $pluralModelLabel = 'laporan masalah pohon';

    protected static ?string $slug = 'laporan-pohon';

    public static function table(Table $table): Table
    {
        return TreeIssuesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['reporter', 'tree', 'assignee']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreeIssues::route('/'),
        ];
    }
}
