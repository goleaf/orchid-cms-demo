<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Support\Crm\PhoneNormalizer;
use Illuminate\Database\Eloquent\Builder;

class DetectLeadDuplicateAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?MarketingLead $currentLead = null): ?MarketingLead
    {
        $email = filled($data['email'] ?? null) ? mb_strtolower((string) $data['email']) : null;
        $normalizedPhone = PhoneNormalizer::normalize($data['phone'] ?? null);

        if (! filled($email) && ! filled($normalizedPhone)) {
            return null;
        }

        $baseQuery = MarketingLead::query()
            ->forLeadList()
            ->when($currentLead?->exists, fn (Builder $query): Builder => $query->whereKeyNot($currentLead->getKey()));

        if (filled($normalizedPhone)) {
            $phoneMatch = (clone $baseQuery)
                ->where('normalized_phone', $normalizedPhone)
                ->orderBy('created_at')
                ->first();

            if ($phoneMatch !== null) {
                return $phoneMatch;
            }
        }

        if (filled($email)) {
            return (clone $baseQuery)
                ->where('email', 'like', $email)
                ->orderBy('created_at')
                ->first();
        }

        return null;
    }
}
