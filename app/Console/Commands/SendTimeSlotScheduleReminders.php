<?php

namespace App\Console\Commands;

use App\Services\TimeSlotScheduleReminderService;
use Illuminate\Console\Command;

class SendTimeSlotScheduleReminders extends Command
{
    protected $signature = 'reminders:timeslot-schedule';

    protected $description = 'Send reminders to facility owners 30 days before future timeslot schedule runs out';

    public function handle(TimeSlotScheduleReminderService $service): int
    {
        $service->run();

        return self::SUCCESS;
    }
}