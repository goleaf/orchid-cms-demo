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
        return (new MailMessage)
            ->subject('New driving school application')
            ->line($this->lead->fullName().' submitted an online enrollment request.')
            ->line('Contact: '.($this->lead->email ?: $this->lead->phone ?: 'not provided'))
            ->line('Preferred time: '.($this->lead->preferred_time ?: 'not provided'))
            ->action('Open leads', route('platform.marketing.leads'));
    }
}
