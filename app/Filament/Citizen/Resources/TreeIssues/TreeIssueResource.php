<?php

namespace App\Filament\Citizen\Resources\TreeIssues;

use App\Filament\Citizen\Resources\TreeIssues\Pages\CreateTreeIssue;
use App\Filament\Citizen\Resources\TreeIssues\Pages\ListTreeIssues;
use App\Filament\Shared\Schemas\TreeIssueForm;
use App\Filament\Shared\Tables\TreeIssueTable;
use App\Models\TreeIssue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TreeIssueResource extends Resource
{
    protected static ?string $model = TreeIssue::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Laporan Pohon';

    protected static ?string $modelLabel = 'Laporan Masalah Pohon';

    protected static ?string $pluralModelLabel = 'Laporan Masalah Pohon';

    protected static ?string $slug = 'laporan-pohon';

    public static function form(Schema $schema): Schema
    {
        return TreeIssueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreeIssueTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->reportedBy(auth()->id() ?? 0)
            ->with(['tree.species']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreeIssues::route('/'),
            'create' => CreateTreeIssue::route('/create'),
        ];
    }
}
