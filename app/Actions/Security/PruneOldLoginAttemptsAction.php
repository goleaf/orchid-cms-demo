<?php

namespace App\Actions\Security;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PruneOldLoginAttemptsAction
{
    public function handle(int $days = 90, ?User $actor = null, ?Request $request = null): int
    {
        if (! Schema::hasTable('login_attempts')) {
            return 0;
        }

        try {
            $deleted = LoginAttempt::query()
                ->where('attempted_at', '<', now()->subDays($days))
                ->delete();

            app(RecordAuditLogAction::class)->handle('login_attempts_pruned', $actor, null, [], [], [
                'days' => $days,
                'deleted' => $deleted,
            ], $request);

            return $deleted;
        } catch (Throwable) {
            return 0;
        }
    }
}
