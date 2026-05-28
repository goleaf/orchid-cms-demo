<?php

namespace App\Rules;

use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchAccessRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === []) {
            return;
        }

        if (! is_array($value)) {
            $fail(tkey('security.validation.branch_access_invalid'));

            return;
        }

        $ids = collect($value)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        if ($ids->count() !== count($value)) {
            $fail(tkey('security.validation.branch_access_invalid'));

            return;
        }

        if (Branch::query()->whereIn('id', $ids->all())->count() !== $ids->count()) {
            $fail(tkey('security.validation.branch_access_invalid'));
        }
    }
}
