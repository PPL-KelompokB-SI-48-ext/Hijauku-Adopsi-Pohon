<?php

namespace App\Filament\Manager\Resources\TreeSpecies\Pages;

use App\Actions\TreeSpecies\CreateTreeSpecies as CreateTreeSpeciesAction;
use App\Filament\Manager\Resources\TreeSpecies\TreeSpeciesResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTreeSpecies extends CreateRecord
{
    protected static ?string $title = 'Tambah Jenis Pohon';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = TreeSpeciesResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateTreeSpeciesAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Jenis pohon berhasil ditambahkan.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan Jenis Pohon');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
