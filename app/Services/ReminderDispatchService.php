<?php

namespace App\Services;

use App\Models\AppointmentReminder;
use App\Services\Contracts\ReminderChannelInterface;

class ReminderDispatchService
{
    public function __construct(private ReminderChannelInterface $channel) {}

    public function dispatchDue(): int
    {
        $due = AppointmentReminder::due()->get();

        foreach ($due as $reminder) {
            $sent = $this->channel->send($reminder);

            $reminder->update([
                'status' => $sent ? AppointmentReminder::StatusSent : AppointmentReminder::StatusFailed,
                'sent_at' => $sent ? now() : null,
            ]);
        }

        return $due->count();
    }
}
