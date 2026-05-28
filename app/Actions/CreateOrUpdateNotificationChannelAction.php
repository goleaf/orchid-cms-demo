<?php

namespace App\Actions;

use App\Models\NotificationChannel;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateNotificationChannelAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(NotificationChannel|int|string|null $channel, array $data): NotificationChannel
    {
        $model = $channel instanceof NotificationChannel
            ? $channel
            : (filled($channel) ? NotificationChannel::query()->findOrFail($channel) : new NotificationChannel);

        if ($model->exists && $model->is_system && array_key_exists('code', $data) && (string) $data['code'] !== (string) $model->code) {
            throw ValidationException::withMessages([
                'channel.code' => tkey('communication.validation.system_channel_code_locked'),
            ]);
        }

        $model->fill($data);
        $model->save();

        return $model->refresh();
    }
}
