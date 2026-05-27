<?php

namespace App\Notifications;

use App\Models\MarketingLead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentLeadSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly MarketingLead $lead) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->localeFor($notifiable);
        $contact = $this->lead->email ?: $this->lead->phone ?: tkey('crm.notifications.enrollment.submitted.empty.not_provided', locale: $locale);
        $preferredTime = $this->lead->preferred_time ?: tkey('crm.notifications.enrollment.submitted.empty.not_provided', locale: $locale);

        return (new MailMessage)
            ->subject(tkey('crm.notifications.enrollment.submitted.subject', locale: $locale))
            ->line(tkey('crm.notifications.enrollment.submitted.lines.request_submitted', [
                'name' => $this->lead->fullName(),
            ], $locale))
            ->line(tkey('crm.notifications.enrollment.submitted.lines.contact', [
                'contact' => $contact,
            ], $locale))
            ->line(tkey('crm.notifications.enrollment.submitted.lines.preferred_time', [
                'time' => $preferredTime,
            ], $locale))
            ->action(tkey('crm.notifications.enrollment.submitted.actions.open_leads', locale: $locale), route('platform.marketing.leads'));
    }

    private function localeFor(object $notifiable): ?string
    {
        if (! method_exists($notifiable, 'getAttribute')) {
            return null;
        }

        $locale = $notifiable->getAttribute('preferred_locale');

        return is_string($locale) && $locale !== '' ? $locale : null;
    }
}
