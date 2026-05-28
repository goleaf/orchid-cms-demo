<?php

namespace App\Actions;

use App\Models\NotificationMessage;

class SendSmsPlaceholderNotificationAction
{
    public function handle(NotificationMessage $message): NotificationMessage
    {
        return app(SendPlaceholderNotificationAction::class)->handle($message, 'sms_placeholder');
    }
}
