<?php

namespace App\Notifications;

use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationMessageMailNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly NotificationMessage $message,
        private readonly NotificationRecipient $recipient,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->message->subject ?: tkey('notifications.messages.fallback_subject', locale: $this->recipient->locale))
            ->line($this->message->body);

        return $mail;
    }
}
