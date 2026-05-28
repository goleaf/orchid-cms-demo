<?php

namespace App\Actions;

use App\Models\NotificationMessage;

class SendWhatsAppPlaceholderNotificationAction
{
    public function handle(NotificationMessage $message): NotificationMessage
    {
        return app(SendPlaceholderNotificationAction::class)->handle($message, 'whatsapp_placeholder');
    }
}
