<?php

namespace App\Filament\FieldStaff\Resources\GrowthObservations\Pages;

use App\Actions\GrowthObservations\CreateGrowthObservation as CreateGrowthObservationAction;
use App\Filament\FieldStaff\Resources\GrowthObservations\GrowthObservationResource;
use App\Models\GrowthObservation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateGrowthObservation extends CreateRecord
{
    protected static string $resource = GrowthObservationResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Catat Pengamatan Pertumbuhan';
    }

    protected function handleRecordCreation(array $data): GrowthObservation
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        try {
            return app(CreateGrowthObservationAction::class)->handle($actor, $data);
        } catch (ValidationException $e) {
            $mapped = [];
            foreach ($e->errors() as $key => $messages) {
                $prefixedKey = str_starts_with($key, 'data.') ? $key : 'data.'.$key;
                $mapped[$prefixedKey] = $messages;
            }

            throw ValidationException::withMessages($mapped);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Pengamatan pertumbuhan berhasil dicatat.')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return GrowthObservationResource::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan pengamatan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
