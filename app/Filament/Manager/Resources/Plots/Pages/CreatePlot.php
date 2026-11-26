<?php

namespace App\Filament\Manager\Resources\Plots\Pages;

use App\Actions\Plots\CreatePlot as CreatePlotAction;
use App\Filament\Manager\Resources\Plots\PlotResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePlot extends CreateRecord
{
    protected static ?string $title = 'Tambah Petak Tanam';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = PlotResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        return app(CreatePlotAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Petak tanam berhasil dicatat.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan petak');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
