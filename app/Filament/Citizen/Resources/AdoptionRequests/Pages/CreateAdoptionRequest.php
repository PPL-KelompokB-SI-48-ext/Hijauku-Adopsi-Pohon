<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests\Pages;

use App\Actions\Adoptions\CreateAdoptionRequestAction;
use App\Filament\Citizen\Resources\AdoptionRequests\AdoptionRequestResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdoptionRequest extends CreateRecord
{
    protected static string $resource = AdoptionRequestResource::class;

    protected static ?string $title = 'Ajukan Adopsi Pohon';

    protected static ?string $breadcrumb = 'Ajukan';

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateAdoptionRequestAction::class)
            ->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return AdoptionRequestResource::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Kirim Permohonan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}