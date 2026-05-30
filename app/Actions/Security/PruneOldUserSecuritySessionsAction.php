<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PruneOldUserSecuritySessionsAction
{
    public function handle(int $days = 90, ?User $actor = null, ?Request $request = null): int
    {
        if (! Schema::hasTable('user_security_sessions')) {
            return 0;
        }

        try {
            $cutoff = now()->subDays($days);
            $deleted = UserSecuritySession::query()
                ->where(function ($query) use ($cutoff): void {
                    $query
                        ->where(function ($query) use ($cutoff): void {
                            $query->whereNotNull('logged_out_at')->where('logged_out_at', '<', $cutoff);
                        })
                        ->orWhere(function ($query) use ($cutoff): void {
                            $query->whereNotNull('revoked_at')->where('revoked_at', '<', $cutoff);
                        });
                })
                ->delete();

            app(RecordAuditLogAction::class)->handle('sessions_pruned', $actor, null, [], [], [
                'days' => $days,
                'deleted' => $deleted,
            ], $request);

            return $deleted;
        } catch (Throwable) {
            return 0;
        }
    }
}
