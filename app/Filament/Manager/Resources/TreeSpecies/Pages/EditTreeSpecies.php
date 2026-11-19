<?php

namespace App\Filament\Manager\Resources\TreeSpecies\Pages;

use App\Actions\TreeSpecies\UpdateTreeSpecies;
use App\Filament\Manager\Resources\TreeSpecies\TreeSpeciesResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTreeSpecies extends EditRecord
{
    protected static ?string $title = 'Edit Jenis Pohon';

    protected static ?string $breadcrumb = 'Edit';

    protected static string $resource = TreeSpeciesResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(UpdateTreeSpecies::class)->handle($actor, $record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Jenis pohon berhasil diperbarui.';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Simpan Perubahan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
