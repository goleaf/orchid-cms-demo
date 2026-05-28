<?php

namespace App\Actions;

use App\Models\NotificationMessage;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use App\Support\Notifications\NotificationTargetResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateMessageFromTemplateAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(NotificationTemplate|int $template, array $data = []): NotificationMessage
    {
        $template = $template instanceof NotificationTemplate
            ? $template
            : NotificationTemplate::query()->findOrFail($template);

        $version = $this->version($template, $data['template_version_id'] ?? null);
        $target = app(NotificationTargetResolver::class)->resolve(
            is_string($data['target_type'] ?? null) ? $data['target_type'] : null,
            $data['target_id'] ?? null,
        );
        $variables = app(ResolveNotificationTemplateVariablesAction::class)->handle(
            $target,
            is_array($data['variables'] ?? null) ? $data['variables'] : [],
        );
        $rendered = app(RenderNotificationTemplateAction::class)->handle($version, $variables, $data['locale'] ?? null);
        $recipients = is_array($data['recipients'] ?? null) ? $data['recipients'] : [];

        if ($target instanceof Model) {
            $recipients[] = [
                ...app(NotificationTargetResolver::class)->recipientPayload($target),
                'target_type' => $data['target_type'] ?? $target->getMorphClass(),
                'target_id' => $target->getKey(),
            ];
        }

        return app(CreateNotificationMessageAction::class)->handle([
            ...$data,
            'channel_id' => $data['channel_id'] ?? $template->channel_id,
            'template_id' => $template->id,
            'template_version_id' => $version->id,
            'subject' => $data['subject'] ?? $rendered['subject'],
            'body' => $data['body'] ?? $rendered['body'],
            'priority' => $data['priority'] ?? NotificationMessage::PRIORITY_NORMAL,
            'recipients' => $recipients,
        ]);
    }

    private function version(NotificationTemplate $template, mixed $versionId): NotificationTemplateVersion
    {
        $query = $template->versions()->published();

        if (filled($versionId)) {
            $query->whereKey($versionId);
        }

        $version = $query->orderByDesc('version')->first();

        if ($version === null) {
            throw ValidationException::withMessages([
                'template_id' => tkey('notifications.validation.template_not_published'),
            ]);
        }

        return $version;
    }
}
