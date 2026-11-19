<?php

namespace App\Console\Commands;

use App\Actions\Adoptions\CompleteExpiredAdoptionAgreementAction;
use App\Models\AdoptionAgreement;
use Illuminate\Console\Command;

class CompleteExpiredAdoptionAgreements extends Command
{
    protected $signature = 'adoptions:complete-expired';

    protected $description = 'Menyelesaikan kesepakatan adopsi yang masa berlakunya telah berakhir.';

    public function handle(CompleteExpiredAdoptionAgreementAction $action): int
    {
        $agreementIds = AdoptionAgreement::query()
            ->where('status', 'active')
            ->whereNotNull('ends_on')
            ->whereDate('ends_on', '<', today())
            ->pluck('id');

        $completed = 0;

        foreach ($agreementIds as $agreementId) {
            $agreement = AdoptionAgreement::query()->find($agreementId);

            if ($agreement && $action->handle($agreement)) {
                $completed++;
            }
        }

        $this->info("{$completed} kesepakatan berhasil diselesaikan.");

        return self::SUCCESS;
    }
}
