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
        $format = $this->lead->preferred_format ?? tkey('crm.notifications.enrollment.auto_reply.empty.not_selected');

        return (new MailMessage)
            ->subject(tkey('crm.notifications.enrollment.auto_reply.subject'))
            ->greeting(tkey('crm.notifications.enrollment.auto_reply.greeting', [
                'name' => $this->lead->first_name,
            ]))
            ->line(tkey('crm.notifications.enrollment.auto_reply.lines.received'))
            ->line(tkey('crm.notifications.enrollment.auto_reply.lines.preferred_format', [
                'format' => $format,
            ]))
            ->action(tkey('crm.notifications.enrollment.auto_reply.actions.open_website'), route('site.home'));
    }
}
