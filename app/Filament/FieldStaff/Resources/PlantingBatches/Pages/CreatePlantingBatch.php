<?php

namespace App\Filament\FieldStaff\Resources\PlantingBatches\Pages;

use App\Actions\PlantingBatches\CreatePlantingBatch as CreatePlantingBatchAction;
use App\Filament\FieldStaff\Resources\PlantingBatches\PlantingBatchResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePlantingBatch extends CreateRecord
{
    protected static ?string $title = 'Catat Pelaksanaan Penanaman';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = PlantingBatchResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreatePlantingBatchAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pelaksanaan penanaman berhasil dicatat.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan Pelaksanaan Penanaman');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}

