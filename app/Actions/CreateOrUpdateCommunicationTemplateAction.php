<?php

namespace App\Actions;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateCommunicationTemplateAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CommunicationTemplate|int|string|null $template, array $data): CommunicationTemplate
    {
        $model = $template instanceof CommunicationTemplate
            ? $template
            : (filled($template) ? CommunicationTemplate::query()->findOrFail($template) : new CommunicationTemplate);

        if ($model->exists && $model->is_system && array_key_exists('code', $data) && (string) $data['code'] !== (string) $model->code) {
            throw ValidationException::withMessages([
                'template.code' => tkey('communication.validation.system_template_code_locked'),
            ]);
        }

        $channel = filled($data['notification_channel_id'] ?? null)
            ? NotificationChannel::query()->find((int) $data['notification_channel_id'])
            : null;

        $data['channel'] = $channel?->code;

        $model->fill($data);
        $model->save();

        return $model->refresh();
    }
}
