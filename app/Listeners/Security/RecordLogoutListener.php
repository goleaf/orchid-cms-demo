<?php

namespace App\Listeners\Security;

use App\Actions\Security\RecordUserLogoutSessionAction;
use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Throwable;

class RecordLogoutListener
{
    public function handle(Logout $event): void
    {
        try {
            app(RecordUserLogoutSessionAction::class)->handle(
                $event->user instanceof User ? $event->user : null,
                null,
                request(),
            );
        } catch (Throwable) {
            //
        }
    }
}
