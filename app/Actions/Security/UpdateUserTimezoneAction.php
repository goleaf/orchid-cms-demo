<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\ValidUserTimezoneRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class UpdateUserTimezoneAction
{
    public function handle(User $user, string $timezone, ?User $actor = null, ?Request $request = null): User
    {
        Validator::make(['timezone' => $timezone], ['timezone' => ['required', 'string', new ValidUserTimezoneRule]])->validate();

        $before = $user->only(['timezone']);

        if (Schema::hasColumn('users', 'timezone')) {
            $user->forceFill(['timezone' => $timezone])->save();
        }

        if (Schema::hasTable('staff_profiles') && $user->staffProfile()->exists()) {
            $user->staffProfile()->update(['timezone' => $timezone, 'updated_by_id' => $actor?->getKey()]);
        }

        app(RecordAuditLogAction::class)->handle(
            'user_timezone_updated',
            $actor,
            $user,
            $before,
            $user->only(['timezone']),
            [],
            $request,
        );

        return $user->refresh();
    }
}
