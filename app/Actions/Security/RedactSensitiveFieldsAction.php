<?php

namespace App\Actions\Security;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class RedactSensitiveFieldsAction
{
    /**
     * @var array<int, string>
     */
    private array $sensitiveFragments = [
        'api_key',
        'authorization',
        'cookie',
        'credentials',
        'current_password',
        'national_id',
        'old_password',
        'password',
        'personal_code',
        'private_key',
        'remember_token',
        'secret',
        'session',
        'ssn',
        'token',
    ];

    public function handle(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return collect($value)
                ->mapWithKeys(fn (mixed $item, mixed $key): array => [
                    $key => $this->isSensitiveKey((string) $key)
                        ? '[redacted]'
                        : $this->handle($item),
                ])
                ->all();
        }

        if (is_string($value)) {
            return Str::limit($value, 500, '...');
        }

        return $value;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::of($key)->lower()->replace(['-', '.'], '_')->toString();

        foreach ($this->sensitiveFragments as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
