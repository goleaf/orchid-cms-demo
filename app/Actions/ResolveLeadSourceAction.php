<?php

namespace App\Actions;

use App\Models\LeadSource;
use Throwable;

class ResolveLeadSourceAction
{
    public function handle(?string $requestedSource, string $fallbackSource = 'website', ?string $formName = null): string
    {
        $candidates = [];

        if ($formName === 'contact') {
            $candidates[] = 'contact_form';
            $candidates[] = 'contact';
        }

        if (filled($requestedSource)) {
            $candidates[] = (string) $requestedSource;
        }

        $candidates[] = $fallbackSource;
        $candidates[] = 'website';

        foreach (array_unique($candidates) as $candidate) {
            if ($this->sourceExists($candidate)) {
                return $candidate;
            }
        }

        return $fallbackSource;
    }

    private function sourceExists(string $code): bool
    {
        try {
            return LeadSource::query()
                ->where('code', $code)
                ->where('is_active', true)
                ->exists();
        } catch (Throwable) {
            return in_array($code, ['website', 'callback', 'contact_form'], true);
        }
    }
}
