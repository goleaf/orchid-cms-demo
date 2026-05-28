<?php

namespace App\Actions\Security;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateStaffProfileAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(StaffProfile $profile, array $attributes, ?User $actor = null, ?Request $request = null): StaffProfile
    {
        return DB::transaction(function () use ($profile, $attributes, $actor, $request): StaffProfile {
            $before = $profile->only(['staff_number', 'branch_id', 'is_visible_on_site', 'preferred_locale', 'timezone']);

            $data = Arr::only($attributes, [
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
            $data['updated_by_id'] = $actor?->getKey();

            $profile->fill($data)->save();

            app(RecordAuditLogAction::class)->handle(
                'staff_profile.updated',
                $actor,
                $profile,
                $before,
                $profile->only(['staff_number', 'branch_id', 'is_visible_on_site', 'preferred_locale', 'timezone']),
                [],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                'staff_profile.updated',
                $profile->user,
                'info',
                ['actor_id' => $actor?->getKey()],
                $request,
            );

            return $profile->refresh();
        });
    }
}
