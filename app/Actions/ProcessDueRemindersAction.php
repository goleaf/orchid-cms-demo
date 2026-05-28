<?php

namespace App\Actions;

use App\Models\NotificationMessage;
use App\Models\ReminderSchedule;
use App\Support\Notifications\NotificationTargetResolver;

class ProcessDueRemindersAction
{
    /**
     * @return array{processed: int, failed: int}
     */
    public function handle(): array
    {
        $processed = 0;
        $failed = 0;

        ReminderSchedule::query()
            ->due()
            ->with(['rule.template.versions', 'message'])
            ->orderBy('scheduled_at')
            ->chunkById(100, function ($schedules) use (&$processed, &$failed): void {
                foreach ($schedules as $schedule) {
                    if ($this->process($schedule)) {
                        $processed++;
                    } else {
                        $failed++;
                    }
                }
            });

        return ['processed' => $processed, 'failed' => $failed];
    }

    private function process(ReminderSchedule $schedule): bool
    {
        $rule = $schedule->rule;
        $template = $rule?->template;
        $target = app(NotificationTargetResolver::class)->resolve($schedule->target_type, $schedule->target_id);

        if ($rule === null || $template === null || $template->channel_id === null || $target === null) {
            $schedule->forceFill([
                'status' => ReminderSchedule::STATUS_FAILED,
                'processed_at' => now(),
                'metadata' => [
                    ...($schedule->metadata ?? []),
                    'error' => tkey('notifications.validation.reminder_cannot_be_processed'),
                ],
            ])->save();

            return false;
        }

        $message = app(CreateMessageFromTemplateAction::class)->handle($template, [
            'channel_id' => $template->channel_id,
            'target_type' => $schedule->target_type,
            'target_id' => $schedule->target_id,
            'status' => NotificationMessage::STATUS_QUEUED,
            'metadata' => [
                'reminder_rule_id' => $rule->id,
                'reminder_schedule_id' => $schedule->id,
            ],
        ]);

        $schedule->forceFill([
            'message_id' => $message->id,
            'status' => ReminderSchedule::STATUS_SENT,
            'processed_at' => now(),
        ])->save();

        return true;
    }
}
