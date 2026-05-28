<?php

namespace App\Actions;

use App\Models\CommunicationMessage;
use App\Models\CommunicationThread;

class AddCommunicationMessageAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CommunicationThread $thread, array $data): CommunicationMessage
    {
        $message = $thread->messages()->create([
            'direction' => $data['direction'],
            'channel_id' => $data['channel_id'],
            'body' => $data['body'],
            'user_id' => $data['user_id'] ?? null,
            'student_id' => $data['student_id'] ?? $thread->student_id,
            'lead_id' => $data['lead_id'] ?? $thread->lead_id,
            'sent_at' => $data['sent_at'] ?? now(),
            'metadata' => $data['metadata'] ?? null,
        ]);

        foreach ($data['attachments'] ?? [] as $attachment) {
            if (! is_array($attachment)) {
                continue;
            }

            $message->attachments()->create([
                'disk' => $attachment['disk'] ?? 'local',
                'path' => $attachment['path'],
                'original_name' => $attachment['original_name'] ?? null,
                'mime_type' => $attachment['mime_type'] ?? null,
                'size' => $attachment['size'] ?? null,
                'metadata' => $attachment['metadata'] ?? null,
            ]);
        }

        return $message->refresh()->loadMissing(['thread', 'channel', 'attachments']);
    }
}
