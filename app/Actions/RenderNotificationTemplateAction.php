<?php

namespace App\Actions;

use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use Illuminate\Validation\ValidationException;

class RenderNotificationTemplateAction
{
    /**
     * @param  array<string, scalar|null>  $variables
     * @return array{subject: string|null, body: string}
     */
    public function handle(
        NotificationTemplate|NotificationTemplateVersion $template,
        array $variables = [],
        ?string $locale = null,
    ): array {
        $version = $template instanceof NotificationTemplateVersion
            ? $template
            : $template->versions()->published()->orderByDesc('version')->first();

        if ($version === null) {
            throw ValidationException::withMessages([
                'template_id' => tkey('notifications.validation.template_not_published'),
            ]);
        }

        return [
            'subject' => $this->replace($version->subject($locale), $variables),
            'body' => $this->replace($version->body($locale) ?? '', $variables) ?? '',
        ];
    }

    /**
     * @param  array<string, scalar|null>  $variables
     */
    private function replace(?string $value, array $variables): ?string
    {
        if ($value === null || $variables === []) {
            return $value;
        }

        $replacements = [];

        foreach ($variables as $key => $replacement) {
            $normalized = (string) $replacement;
            $replacements[':'.$key] = $normalized;
            $replacements['{{'.$key.'}}'] = $normalized;
            $replacements['{{ '.$key.' }}'] = $normalized;
        }

        return strtr($value, $replacements);
    }
}
