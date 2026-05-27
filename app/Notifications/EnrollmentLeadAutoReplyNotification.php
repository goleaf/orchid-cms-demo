<?php

namespace App\Notifications;

use App\Models\MarketingLead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentLeadAutoReplyNotification extends Notification
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
            ->subject('DrivePro Academy application received')
            ->greeting('Hello '.$this->lead->first_name)
            ->line('We received your application and will contact you to confirm the program, branch, group, and preferred schedule.')
            ->line('Preferred format: '.($this->lead->preferred_format ?? 'not selected'))
            ->action('Open website', route('site.home'));
    }
}
