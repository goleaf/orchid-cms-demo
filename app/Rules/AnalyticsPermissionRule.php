<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class AnalyticsPermissionRule implements ValidationRule
{
    /**
     * @param  array<int, string>|string|null  $permissions
     */
    public function __construct(
        private readonly ?User $user = null,
        private readonly array|string|null $permissions = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $permissions = $this->requiredPermissions($value);

        if ($permissions === []) {
            return;
        }

        $user = $this->user ?? Auth::user();

        if (! $user instanceof User) {
            $fail(tkey('analytics.validation.permission_denied'));

            return;
        }

        foreach ($permissions as $permission) {
            if (! $user->hasAccess($permission)) {
                $fail(tkey('analytics.validation.permission_denied'));

                return;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function requiredPermissions(mixed $value): array
    {
        $permissions = $this->permissions ?? $value;

        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        if (! is_array($permissions)) {
            return [];
        }

        return array_values(array_filter(
            $permissions,
            fn (mixed $permission): bool => is_string($permission) && $permission !== '',
        ));
    }
}
