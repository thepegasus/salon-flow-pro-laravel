<?php

namespace App\Services\Contracts;

use App\Models\AppointmentReminder;

interface ReminderChannelInterface
{
    public function send(AppointmentReminder $reminder): bool;
}
