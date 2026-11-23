<?php

namespace App\Filament\Manager\Resources\AdoptionAgreements;

use App\Filament\Manager\Resources\AdoptionAgreements\Pages\CreateAdoptionAgreement;
use App\Filament\Manager\Resources\AdoptionAgreements\Pages\ListAdoptionAgreements;
use App\Filament\Manager\Resources\AdoptionAgreements\Schemas\AdoptionAgreementForm;
use App\Filament\Manager\Resources\AdoptionAgreements\Tables\AdoptionAgreementsTable;
use App\Models\AdoptionAgreement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AdoptionAgreementResource extends Resource
{
    protected static ?string $model = AdoptionAgreement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Kesepakatan Adopsi';

    protected static string|UnitEnum|null $navigationGroup = 'Adopsi';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'kesepakatan adopsi';

    protected static ?string $pluralModelLabel = 'kesepakatan adopsi';

    public static function form(Schema $schema): Schema
    {
        return AdoptionAgreementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdoptionAgreementsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tree.species', 'adopter', 'adoptionRequest']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdoptionAgreements::route('/'),
            'create' => CreateAdoptionAgreement::route('/create'),
        ];
    }
}
