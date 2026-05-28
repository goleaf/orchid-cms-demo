<?php

namespace App\Actions;

use App\Models\NotificationDeliveryLog;
use Illuminate\Database\Eloquent\Model;

class LogNotificationDeliveryAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?Model $notifiable = null): NotificationDeliveryLog
    {
        if ($notifiable !== null) {
            $data['notifiable_type'] = $notifiable->getMorphClass();
            $data['notifiable_id'] = $notifiable->getKey();
        }

        return NotificationDeliveryLog::query()->create($data)->refresh();
    }
}
