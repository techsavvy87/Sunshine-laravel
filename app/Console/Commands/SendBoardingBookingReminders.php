<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentBookingNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBoardingBookingReminders extends Command
{
    protected $signature = 'reminders:boarding-bookings';

    protected $description = 'Send owner and staff reminders 1 day before boarding start dates';

    public function handle(AppointmentBookingNotifier $bookingNotifier): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $appointments = Appointment::with([
                'customer.profile',
                'pet',
                'staff.profile',
                'service.category',
                'kennel',
                'catRoom',
            ])
            ->whereDate('date', $tomorrow)
            ->where('status', 'checked_in')
            ->whereNotNull('end_date')
            ->whereHas('service.category', function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%boarding%']);
            })
            ->get();

        $sentCount = 0;

        foreach ($appointments as $appointment) {
            if ($bookingNotifier->sendReminder($appointment, Carbon::parse($tomorrow))) {
                $sentCount++;
                $this->info('Boarding reminder sent for appointment #' . $appointment->id);
            }
        }

        $this->info('Total boarding reminders sent: ' . $sentCount);

        return self::SUCCESS;
    }
}