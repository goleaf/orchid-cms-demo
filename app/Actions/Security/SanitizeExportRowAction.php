<?php

namespace App\Actions\Security;

class SanitizeExportRowAction
{
    /**
     * @param  array<int|string, mixed>  $row
     * @return array<int|string, mixed>
     */
    public function handle(array $row): array
    {
        $redacted = app(RedactSensitiveFieldsAction::class)->handle($row);

        return collect($redacted)
            ->map(fn (mixed $value): mixed => is_string($value) ? $this->sanitizeCell($value) : $value)
            ->all();
    }

    private function sanitizeCell(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
