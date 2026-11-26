<?php

namespace App\Filament\Manager\Resources\FieldAssignments\Pages;

use App\Actions\FieldAssignments\CreateFieldAssignment as CreateFieldAssignmentAction;
use App\Filament\Manager\Resources\FieldAssignments\FieldAssignmentResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFieldAssignment extends CreateRecord
{
    protected static ?string $title = 'Tetapkan Petugas Lapangan';

    protected static ?string $breadcrumb = 'Tetapkan';

    protected static string $resource = FieldAssignmentResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateFieldAssignmentAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Petugas lapangan berhasil ditugaskan pada pohon terpilih.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Tetapkan Penugasan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
