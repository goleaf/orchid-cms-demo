<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\ValidUserLocaleRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class UpdateUserLocaleAction
{
    public function handle(User $user, string $locale, ?User $actor = null, ?Request $request = null): User
    {
        Validator::make(['locale' => $locale], ['locale' => ['required', 'string', new ValidUserLocaleRule]])->validate();

        $before = $user->only(['preferred_locale']);

        if (Schema::hasColumn('users', 'preferred_locale')) {
            $user->forceFill(['preferred_locale' => $locale])->save();
        }

        if (Schema::hasTable('staff_profiles') && $user->staffProfile()->exists()) {
            $user->staffProfile()->update(['preferred_locale' => $locale, 'updated_by_id' => $actor?->getKey()]);
        }

        app(RecordAuditLogAction::class)->handle(
            'user_locale_updated',
            $actor,
            $user,
            $before,
            $user->only(['preferred_locale']),
            [],
            $request,
        );

        return $user->refresh();
    }
}
