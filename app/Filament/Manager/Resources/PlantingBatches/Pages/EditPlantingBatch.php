<?php

namespace App\Filament\Manager\Resources\PlantingBatches\Pages;

use App\Actions\PlantingBatches\UpdatePlantingBatch;
use App\Filament\Manager\Resources\PlantingBatches\PlantingBatchResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPlantingBatch extends EditRecord
{
    protected static ?string $title = 'Edit Kelompok Penanaman';

    protected static ?string $breadcrumb = 'Edit';

    protected static string $resource = PlantingBatchResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(UpdatePlantingBatch::class)->handle($actor, $record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Kelompok penanaman berhasil diperbarui.';
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

