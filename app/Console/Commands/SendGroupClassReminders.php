<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\User;
use App\Models\GroupClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\GroupClassReminderMail;

class SendGroupClassReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:group-class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to customers 1 day before their upcoming group class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Get all group class appointments scheduled for tomorrow
        $appointments = Appointment::with(['customer.profile', 'pet', 'service.category'])
            ->where('date', $tomorrow)
            ->where('status', 'checked_in')
            ->whereHas('service.category', function($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%group%']);
            })
            ->get();

        // Add the class name to the appointment object
        foreach($appointments as $appointment) {
            $appointment->class_name = optional(GroupClass::find($appointment->metadata['group_class_ids'] ?? null))->name ?? '';
        }

        $sentCount = 0;

        foreach ($appointments as $appointment) {
            if (!$appointment->customer || !$appointment->customer->email) {
                continue;
            }

            try {
                Mail::to($appointment->customer->email)->send(new GroupClassReminderMail($appointment));
                $sentCount++;
                $this->info("Reminder sent to {$appointment->customer->email} for appointment #{$appointment->id}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for appointment #{$appointment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Total reminders sent: {$sentCount}");
        return Command::SUCCESS;
    }
}
