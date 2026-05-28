<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateUserStatusAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(UserStatus|int|string|null $status, array $data, ?User $actor = null): UserStatus
    {
        $model = $status instanceof UserStatus
            ? $status
            : (filled($status) ? UserStatus::query()->findOrFail($status) : new UserStatus);

        $this->assertSaveAllowed($model, $data);

        return DB::transaction(function () use ($model, $data, $actor): UserStatus {
            $before = $model->exists ? $model->only(['code', 'is_default', 'is_active', 'is_blocked', 'is_archived']) : [];

            $model->fill($data);
            $model->save();

            if ($model->is_default) {
                app(SetDefaultUserStatusAction::class)->handle($model, $actor);
            }

            app(RecordAuditLogAction::class)->handle(
                $before === [] ? 'user_status.created' : 'user_status.updated',
                $actor,
                $model,
                $before,
                $model->only(['code', 'is_default', 'is_active', 'is_blocked', 'is_archived']),
            );

            return $model->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertSaveAllowed(UserStatus $status, array $data): void
    {
        if ((bool) ($data['is_default'] ?? false) && ! (bool) ($data['is_active'] ?? true)) {
            throw ValidationException::withMessages([
                'status.is_default' => tkey('security.validation.default_user_status_inactive'),
            ]);
        }

        if ($status->exists && $status->is_default && ! (bool) ($data['is_default'] ?? false) && ! $this->anotherDefaultStatusExists($status)) {
            throw ValidationException::withMessages([
                'status.is_default' => tkey('security.validation.default_user_status_required'),
            ]);
        }
    }

    private function anotherDefaultStatusExists(UserStatus $status): bool
    {
        return UserStatus::query()
            ->whereKeyNot($status->getKey())
            ->where('is_default', true)
            ->exists();
    }
}
