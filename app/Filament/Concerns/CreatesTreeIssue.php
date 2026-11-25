<?php

namespace App\Filament\Concerns;

use App\Actions\TreeIssues\CreateTreeIssue as CreateTreeIssueAction;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

trait CreatesTreeIssue
{
    protected function handleRecordCreation(array $data): Model
    {
        $reporter = auth()->user();

        abort_unless($reporter instanceof User, 403);

        return app(CreateTreeIssueAction::class)->handle($reporter, $data);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Kirim Laporan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Laporan masalah pohon berhasil dikirim.')
            ->success();
    }
}
