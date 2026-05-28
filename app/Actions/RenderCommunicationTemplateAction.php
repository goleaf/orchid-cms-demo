<?php

namespace App\Actions;

use App\Models\CommunicationTemplate;

class RenderCommunicationTemplateAction
{
    /**
     * @param  array<string, scalar|null>  $variables
     * @return array{subject: string|null, body: string|null}
     */
    public function handle(CommunicationTemplate $template, array $variables = [], ?string $locale = null): array
    {
        return [
            'subject' => $this->replace($template->subject($locale), $variables),
            'body' => $this->replace($template->body($locale), $variables),
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
