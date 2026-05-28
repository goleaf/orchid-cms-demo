<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetDefaultUserStatusAction
{
    public function handle(UserStatus|int|string $status, ?User $actor = null): UserStatus
    {
        $model = $this->status($status);

        if ($model === null) {
            throw ValidationException::withMessages([
                'status.id' => tkey('security.validation.user_status_invalid'),
            ]);
        }

        if (! $model->is_active) {
            throw ValidationException::withMessages([
                'status.is_default' => tkey('security.validation.default_user_status_inactive'),
            ]);
        }

        return DB::transaction(function () use ($model, $actor): UserStatus {
            UserStatus::query()
                ->whereKeyNot($model->getKey())
                ->update(['is_default' => false]);

            $model->forceFill(['is_default' => true])->save();

            app(RecordAuditLogAction::class)->handle(
                'user_status.default_changed',
                $actor,
                $model,
                [],
                ['status_code' => $model->code],
            );

            return $model->refresh();
        });
    }

    private function status(UserStatus|int|string $status): ?UserStatus
    {
        if ($status instanceof UserStatus) {
            return $status;
        }

        return UserStatus::query()
            ->when(is_numeric($status), fn ($query) => $query->whereKey((int) $status))
            ->when(! is_numeric($status), fn ($query) => $query->where('code', (string) $status))
            ->first();
    }
}
