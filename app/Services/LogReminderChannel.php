<?php

namespace App\Services;

use App\Models\AppointmentReminder;
use App\Services\Contracts\ReminderChannelInterface;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder channel used until a real WhatsApp/SMS provider (Meta Cloud
 * API, Gupshup, Twilio, etc.) is selected and configured. Logs the intent
 * to send so the reminder pipeline is fully wired and testable end to end.
 */
class LogReminderChannel implements ReminderChannelInterface
{
    public function send(AppointmentReminder $reminder): bool
    {
        Log::info('Reminder would be sent', [
            'appointment_id' => $reminder->appointment_id,
            'type' => $reminder->type,
            'channel' => $reminder->channel,
        ]);

        return true;
    }
}
