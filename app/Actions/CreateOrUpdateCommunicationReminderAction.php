<?php

namespace App\Actions;

use App\Models\CommunicationReminder;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Model;

class CreateOrUpdateCommunicationReminderAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CommunicationReminder|int|string|null $reminder, array $data, ?Model $remindable = null): CommunicationReminder
    {
        $model = $reminder instanceof CommunicationReminder
            ? $reminder
            : (filled($reminder) ? CommunicationReminder::query()->findOrFail($reminder) : new CommunicationReminder);

        if ($remindable !== null) {
            $data['remindable_type'] = $remindable->getMorphClass();
            $data['remindable_id'] = $remindable->getKey();

            if ($remindable instanceof MarketingLead) {
                $data['marketing_lead_id'] = $remindable->id;
            }

            if ($remindable instanceof Student || $remindable instanceof StudentProfile) {
                $data['student_profile_id'] = $remindable->id;
            }
        }

        $model->fill($data);
        $model->save();

        return $model->refresh();
    }
}
