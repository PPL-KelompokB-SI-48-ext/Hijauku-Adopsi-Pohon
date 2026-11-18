<?php

namespace App\Actions\Sites;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeleteUnusedSite
{
    public function handle(User $actor, Site $site): void
    {
        DB::transaction(function () use ($actor, $site): void {
            $lockedSite = Site::query()
                ->lockForUpdate()
                ->findOrFail($site->getKey());

            Gate::forUser($actor)->authorize('delete', $lockedSite);

            if ($lockedSite->plots()->exists()) {
                throw ValidationException::withMessages([
                    'site' => 'Lokasi tidak dapat dihapus karena masih digunakan oleh petak tanam.',
                ]);
            }

            if ($lockedSite->activities()->exists()) {
                throw ValidationException::withMessages([
                    'site' => 'Lokasi tidak dapat dihapus karena masih digunakan oleh kegiatan.',
                ]);
            }

            $lockedSite->delete();
        });
    }
}
