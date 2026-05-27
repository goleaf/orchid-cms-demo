<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Collection;

class ResolveLeadNotificationRecipientsAction
{
    /**
     * @return Collection<int, User>
     */
    public function handle(?User $preferredRecipient = null): Collection
    {
        $recipients = User::query()
            ->select(['id', 'name', 'email', 'permissions'])
            ->with(['roles' => fn ($query) => $query->select(['id', 'name', 'slug', 'permissions'])])
            ->whereNotNull('email')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->filter(fn (User $user): bool => $user->hasAccess('crm.leads.view') || $user->hasAccess('website.view_leads'));

        if ($preferredRecipient !== null && ! $recipients->contains('id', $preferredRecipient->id)) {
            $recipients->prepend($preferredRecipient);
        }

        return $recipients
            ->unique('id')
            ->values();
    }
}
