<?php

namespace App\Actions\Security;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateStaffProfileAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes, ?User $actor = null, ?Request $request = null): StaffProfile
    {
        return DB::transaction(function () use ($user, $attributes, $actor, $request): StaffProfile {
            $data = $this->profileData($attributes);
            $data['user_id'] = $user->getKey();
            $data['created_by_id'] = $actor?->getKey();
            $data['updated_by_id'] = $actor?->getKey();

            if (blank($data['staff_number'] ?? null)) {
                $data['staff_number'] = app(GenerateStaffNumberAction::class)->handle();
            }

            $profile = StaffProfile::query()->create($data);

            app(RecordAuditLogAction::class)->handle(
                'staff_profile.created',
                $actor,
                $profile,
                [],
                $profile->only(['user_id', 'staff_number', 'branch_id', 'is_visible_on_site']),
                [],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                'staff_profile.created',
                $user,
                'info',
                ['actor_id' => $actor?->getKey()],
                $request,
            );

            return $profile->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function profileData(array $attributes): array
    {
        return Arr::only($attributes, [
            'staff_number',
            'branch_id',
            'display_name_translations',
            'job_title_translations',
            'public_bio_translations',
            'phone',
            'work_email',
            'preferred_locale',
            'timezone',
            'avatar',
            'is_visible_on_site',
            'internal_notes',
        ]);
    }
}
