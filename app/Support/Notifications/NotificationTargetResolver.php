<?php

namespace App\Support\Notifications;

use App\Models\Lead;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationTargetResolver
{
    /**
     * @return class-string<Model>|null
     */
    public function modelClass(?string $targetType): ?string
    {
        return match ($targetType) {
            'user', 'users', User::class => User::class,
            'student', 'students', 'student_profile', 'student_profiles', Student::class, StudentProfile::class => Student::class,
            'lead', 'leads', 'marketing_lead', 'marketing_leads', Lead::class, MarketingLead::class => Lead::class,
            default => null,
        };
    }

    public function resolve(?string $targetType, mixed $targetId): ?Model
    {
        $modelClass = $this->modelClass($targetType);

        if ($modelClass === null || ! filled($targetId)) {
            return null;
        }

        return $modelClass::query()->whereKey($targetId)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function recipientPayload(Model $target): array
    {
        if ($target instanceof User) {
            return [
                'user_id' => $target->id,
                'email' => $target->email,
                'locale' => $target->preferred_locale,
            ];
        }

        if ($target instanceof StudentProfile) {
            return [
                'student_id' => $target->id,
                'email' => $target->email,
                'phone' => $target->phone,
                'locale' => $target->locale,
            ];
        }

        if ($target instanceof MarketingLead) {
            return [
                'lead_id' => $target->id,
                'email' => $target->email,
                'phone' => $target->phone,
                'locale' => $target->locale,
            ];
        }

        return [];
    }

    /**
     * @return array<string, scalar|null>
     */
    public function variables(Model $target): array
    {
        if ($target instanceof User) {
            return [
                'user_name' => $target->name,
                'user_email' => $target->email,
                'target_name' => $target->name,
                'target_email' => $target->email,
                'target_phone' => null,
            ];
        }

        if ($target instanceof StudentProfile) {
            $name = method_exists($target, 'fullName') ? $target->fullName() : (string) $target->getAttribute('full_name');

            return [
                'student_name' => $name,
                'student_email' => $target->email,
                'student_phone' => $target->phone,
                'target_name' => $name,
                'target_email' => $target->email,
                'target_phone' => $target->phone,
            ];
        }

        if ($target instanceof MarketingLead) {
            $name = method_exists($target, 'fullName') ? $target->fullName() : (string) $target->getAttribute('full_name');

            return [
                'lead_name' => $name,
                'lead_email' => $target->email,
                'lead_phone' => $target->phone,
                'target_name' => $name,
                'target_email' => $target->email,
                'target_phone' => $target->phone,
            ];
        }

        return [];
    }
}
