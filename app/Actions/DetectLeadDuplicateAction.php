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

        return MarketingLead::query()
            ->forLeadList()
            ->when($currentLead?->exists, fn (Builder $query): Builder => $query->whereKeyNot($currentLead->getKey()))
            ->where(function (Builder $query) use ($email, $normalizedPhone): void {
                if (filled($email)) {
                    $query->where('email', $email);
                }

                if (filled($normalizedPhone)) {
                    $method = filled($email) ? 'orWhere' : 'where';
                    $query->{$method}('normalized_phone', $normalizedPhone);
                }
            })
            ->orderBy('created_at')
            ->first();
    }
}
