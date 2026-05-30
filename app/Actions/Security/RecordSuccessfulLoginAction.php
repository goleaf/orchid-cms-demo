<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordSuccessfulLoginAction
{
    public function handle(User $user, ?Request $request = null, ?string $guard = null, array $metadata = []): array
    {
        $request ??= request();

        try {
            $attempt = app(RecordLoginAttemptAction::class)->handle(
                $user,
                $user->email,
                true,
                null,
                $request,
                $guard,
                metadata: $metadata,
            );

            if (Schema::hasColumn('users', 'last_login_at') || Schema::hasColumn('users', 'last_seen_at')) {
                $values = [];

                if (Schema::hasColumn('users', 'last_login_at')) {
                    $values['last_login_at'] = now();
                }

                if (Schema::hasColumn('users', 'last_seen_at')) {
                    $values['last_seen_at'] = now();
                }

                $user->forceFill($values)->saveQuietly();
            }

            $session = app(RecordUserLoginSessionAction::class)->handle($user, null, $request, $guard, $metadata);

            return ['attempt' => $attempt, 'session' => $session];
        } catch (Throwable) {
            return ['attempt' => null, 'session' => null];
        }
    }
}
