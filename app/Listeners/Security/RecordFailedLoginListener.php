<?php

namespace App\Listeners\Security;

use App\Actions\Security\RecordFailedLoginAction;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Throwable;

class RecordFailedLoginListener
{
    public function handle(Failed $event): void
    {
        try {
            $email = is_array($event->credentials)
                ? (string) ($event->credentials['email'] ?? $event->credentials['login'] ?? '')
                : null;

            app(RecordFailedLoginAction::class)->handle(
                $event->user instanceof User ? $event->user : null,
                $email,
                null,
                request(),
                $event->guard,
            );
        } catch (Throwable) {
            //
        }
    }
}
