<?php

namespace App\Listeners\Security;

use App\Actions\Security\RecordSuccessfulLoginAction;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Throwable;

class RecordSuccessfulLoginListener
{
    public function handle(Login $event): void
    {
        try {
            if ($event->user instanceof User) {
                app(RecordSuccessfulLoginAction::class)->handle($event->user, request(), $event->guard);
            }
        } catch (Throwable) {
            //
        }
    }
}
