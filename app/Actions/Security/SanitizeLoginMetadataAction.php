<?php

namespace App\Actions\Security;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class SanitizeLoginMetadataAction
{
    public const REDACTED = '[REDACTED]';

    public const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'access_token',
        'refresh_token',
        'remember_token',
        'api_key',
        'secret',
        'client_secret',
        'private_key',
        'recovery_code',
        'two_factor_secret',
        'session_id',
        'raw_session_id',
        'cookie',
        'authorization',
        'bearer_token',
    ];

    public function handle(mixed $metadata, int $maxItems = 60): array
    {
        if ($metadata instanceof Arrayable) {
            $metadata = $metadata->toArray();
        }

        if (! is_array($metadata)) {
            return [];
        }

        return $this->sanitizeArray($metadata, $maxItems);
    }

    /**
     * @param  array<mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitizeArray(array $metadata, int $maxItems): array
    {
        $safe = [];
        $count = 0;

        foreach ($metadata as $key => $value) {
            if ($count >= $maxItems) {
                $safe['_truncated_items'] = count($metadata) - $maxItems;
                break;
            }

            $safeKey = Str::limit((string) $key, 120, '');
            $safe[$safeKey] = $this->isSensitiveKey($safeKey)
                ? self::REDACTED
                : $this->sanitizeValue($value, $maxItems);
            $count++;
        }

        return $safe;
    }

    private function sanitizeValue(mixed $value, int $maxItems): mixed
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return $this->sanitizeArray($value, $maxItems);
        }

        if (is_string($value)) {
            return Str::limit(str_replace("\0", '', $value), 500, '...');
        }

        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        return Str::limit((string) $value, 500, '...');
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::of($key)->lower()->replace(['-', '.', ' '], '_')->toString();

        foreach (self::SENSITIVE_FIELDS as $field) {
            if ($normalized === $field || str_contains($normalized, $field)) {
                return true;
            }
        }

        return false;
    }
}
