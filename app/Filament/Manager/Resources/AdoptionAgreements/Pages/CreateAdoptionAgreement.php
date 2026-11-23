<?php

namespace App\Filament\Manager\Resources\AdoptionAgreements\Pages;

use App\Actions\Adoptions\CreateAdoptionAgreementAction;
use App\Filament\Manager\Resources\AdoptionAgreements\AdoptionAgreementResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdoptionAgreement extends CreateRecord
{
    protected static ?string $title = 'Buat Kesepakatan Adopsi';

    protected static ?string $breadcrumb = 'Buat Kesepakatan';

    protected static string $resource = AdoptionAgreementResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateAdoptionAgreementAction::class)->handle(
            $actor,
            $data
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Kesepakatan adopsi berhasil dibuat.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan Kesepakatan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
