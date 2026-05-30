<?php

namespace App\Actions\Security;

use App\Models\StaffProfile;
use App\Models\User;
use App\Rules\StaffPhoneRule;
use App\Rules\StaffWorkEmailRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateStaffProfileAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(StaffProfile $profile, array $attributes, ?User $actor = null, ?Request $request = null): StaffProfile
    {
        Validator::make($attributes, [
            'work_email' => ['nullable', 'string', 'max:255', new StaffWorkEmailRule($profile)],
            'phone' => ['nullable', 'string', 'max:64', new StaffPhoneRule],
            'preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
        ])->validate();

        return DB::transaction(function () use ($profile, $attributes, $actor, $request): StaffProfile {
            $before = $profile->only(['staff_number', 'branch_id', 'phone', 'work_email', 'is_visible_on_site', 'preferred_locale', 'timezone']);

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
                'staff_profile_updated',
                $actor,
                $profile,
                $before,
                $profile->only(['staff_number', 'branch_id', 'phone', 'work_email', 'is_visible_on_site', 'preferred_locale', 'timezone']),
                [],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                'staff_profile_updated',
                $profile->user,
                'info',
                ['actor_id' => $actor?->getKey()],
                $request,
            );

            return $profile->refresh();
        });
    }
}
