<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FilterLeadsAction
{
    /**
     * @param  Builder<\App\Models\MarketingLead>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<\App\Models\MarketingLead>
     */
    public function handle(Builder $query, array $filters, ?User $user = null, bool $canViewMarketing = false): Builder
    {
        return $query
            ->when($this->filled($filters, 'search'), fn (Builder $query): Builder => $query->matchingSearch($this->value($filters, 'search')))
            ->when($this->filled($filters, 'status'), fn (Builder $query): Builder => $query->where('status', $this->value($filters, 'status')))
            ->when($this->filled($filters, 'source'), fn (Builder $query): Builder => $query->where('source', $this->value($filters, 'source')))
            ->when($this->filled($filters, 'manager_id'), fn (Builder $query): Builder => $query->where('responsible_manager_id', $this->value($filters, 'manager_id')))
            ->when($this->filled($filters, 'course_id', 'training_program_id'), fn (Builder $query): Builder => $query->where('training_program_id', $this->value($filters, 'course_id', 'training_program_id')))
            ->when($this->filled($filters, 'course_category_id'), fn (Builder $query): Builder => $query->where('course_category_id', $this->value($filters, 'course_category_id')))
            ->when($this->filled($filters, 'branch_id'), fn (Builder $query): Builder => $query->where('branch_id', $this->value($filters, 'branch_id')))
            ->when($this->filled($filters, 'training_group_id'), fn (Builder $query): Builder => $query->where('training_group_id', $this->value($filters, 'training_group_id')))
            ->when($this->filled($filters, 'tag_id'), fn (Builder $query): Builder => $query->whereHas('tags', fn (Builder $query): Builder => $query->whereKey($this->value($filters, 'tag_id'))))
            ->when($this->filled($filters, 'tag_slug'), fn (Builder $query): Builder => $query->whereHas('tags', fn (Builder $query): Builder => $query->where('slug', $this->value($filters, 'tag_slug'))))
            ->when($this->filled($filters, 'lost_reason', 'lost_reason_code'), fn (Builder $query): Builder => $query->where('lost_reason_code', $this->value($filters, 'lost_reason', 'lost_reason_code')))
            ->when($this->filled($filters, 'priority'), fn (Builder $query): Builder => $query->where('priority', $this->value($filters, 'priority')))
            ->when($this->filled($filters, 'created_from'), fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->value($filters, 'created_from')))
            ->when($this->filled($filters, 'created_to'), fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->value($filters, 'created_to')))
            ->when($this->filled($filters, 'next_follow_up_from'), fn (Builder $query): Builder => $query->whereDate('next_follow_up_at', '>=', $this->value($filters, 'next_follow_up_from')))
            ->when($this->filled($filters, 'next_follow_up_to'), fn (Builder $query): Builder => $query->whereDate('next_follow_up_at', '<=', $this->value($filters, 'next_follow_up_to')))
            ->when($this->filled($filters, 'last_contacted_from'), fn (Builder $query): Builder => $query->whereDate('last_contacted_at', '>=', $this->value($filters, 'last_contacted_from')))
            ->when($this->filled($filters, 'last_contacted_to'), fn (Builder $query): Builder => $query->whereDate('last_contacted_at', '<=', $this->value($filters, 'last_contacted_to')))
            ->when($canViewMarketing && $this->filled($filters, 'utm_source'), fn (Builder $query): Builder => $query->where('utm_source', $this->value($filters, 'utm_source')))
            ->when($canViewMarketing && $this->filled($filters, 'utm_medium'), fn (Builder $query): Builder => $query->where('utm_medium', $this->value($filters, 'utm_medium')))
            ->when($canViewMarketing && $this->filled($filters, 'utm_campaign'), fn (Builder $query): Builder => $query->where('utm_campaign', $this->value($filters, 'utm_campaign')))
            ->when($this->filled($filters, 'form_name'), fn (Builder $query): Builder => $query->where('form_name', $this->value($filters, 'form_name')))
            ->when($this->flag($filters, 'only_my') && $user !== null, fn (Builder $query): Builder => $query->assignedTo($user->id))
            ->when($this->flag($filters, 'only_unassigned'), fn (Builder $query): Builder => $query->unassigned())
            ->when($this->flag($filters, 'only_overdue', 'overdue'), fn (Builder $query): Builder => $this->applyOverdue($query))
            ->when($this->flag($filters, 'only_due_today', 'due_today'), fn (Builder $query): Builder => $query->dueToday())
            ->when($this->flag($filters, 'only_duplicates'), fn (Builder $query): Builder => $query->duplicates())
            ->when($this->flag($filters, 'only_open'), fn (Builder $query): Builder => $query->open())
            ->when($this->flag($filters, 'only_closed'), fn (Builder $query): Builder => $query->closed())
            ->when($this->flag($filters, 'only_converted'), fn (Builder $query): Builder => $query->converted())
            ->when($this->flag($filters, 'only_not_converted'), fn (Builder $query): Builder => $query->notConverted())
            ->when($this->flag($filters, 'hot'), fn (Builder $query): Builder => $this->applyHot($query))
            ->when($this->filled($filters, 'segment'), fn (Builder $query): Builder => $this->applySegment($query, $this->value($filters, 'segment'), $user));
    }

    private function applySegment(Builder $query, string $segment, ?User $user): Builder
    {
        return match ($segment) {
            'all' => $query,
            'new' => $query->new(),
            'my', 'my_leads' => $user === null ? $query : $query->assignedTo($user->id),
            'unassigned' => $query->unassigned(),
            'today', 'call_today' => $query->dueToday(),
            'overdue' => $this->applyOverdue($query),
            'waiting_payment' => $query->where('status', LeadStatus::WaitingPayment->value),
            'waiting_documents' => $query->where('status', LeadStatus::WaitingDocuments->value),
            'hot' => $this->applyHot($query),
            'duplicate', 'duplicates' => $query->duplicates(),
            'lost' => $query->lost(),
            'spam' => $query->spam(),
            'converted' => $query->converted(),
            'ready_to_enroll' => $query->where('status', LeadStatus::ReadyToEnroll->value),
            'open' => $query->open(),
            'closed' => $query->closed(),
            'not_converted' => $query->notConverted(),
            default => $query,
        };
    }

    private function applyOverdue(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->overdueFollowUp()
                ->orWhere(function (Builder $query): void {
                    $query
                        ->open()
                        ->whereHas('overdueTasks');
                });
        });
    }

    private function applyHot(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('is_hot', true)
                ->orWhereIn('priority', ['high', 'urgent'])
                ->orWhereHas('tags', fn (Builder $query): Builder => $query->where('slug', 'hot'));
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filled(array $filters, string ...$keys): bool
    {
        return filled($this->value($filters, ...$keys));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function flag(array $filters, string ...$keys): bool
    {
        return in_array($this->value($filters, ...$keys), ['1', 1, true, 'true'], true);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function value(array $filters, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (filled($filters[$key] ?? null)) {
                return is_string($filters[$key]) ? trim($filters[$key]) : $filters[$key];
            }
        }

        return '';
    }
}
