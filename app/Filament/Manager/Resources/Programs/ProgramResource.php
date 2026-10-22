<?php

namespace App\Filament\Manager\Resources\Programs;

use App\Filament\Manager\Resources\Programs\Pages\CreateProgram;
use App\Filament\Manager\Resources\Programs\Pages\ListPrograms;
use App\Filament\Manager\Resources\Programs\Schemas\ProgramForm;
use App\Filament\Manager\Resources\Programs\Tables\ProgramsTable;
use App\Models\Program;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Program Penghijauan';

    protected static string|UnitEnum|null $navigationGroup = 'Program & Lokasi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'program';

    protected static ?string $pluralModelLabel = 'program penghijauan';

    public static function form(Schema $schema): Schema
    {
        return ProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        // Programs are shared master data across active managers. The policy
        // restricts access to managers; created_by records attribution.
        return parent::getEloquentQuery()->with('creator');
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
            'index' => ListPrograms::route('/'),
            'create' => CreateProgram::route('/create'),
        ];
    }
}
