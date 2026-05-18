<?php

namespace App\Services;

use App\Mail\TimeSlotScheduleReminderMail;
use App\Models\Notification;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TimeSlotScheduleReminderService
{
    public function run(?Carbon $currentDate = null): array
    {
        $currentDate = ($currentDate ?: Carbon::now())->copy()->startOfDay();
        $scheduleEndDate = TimeSlot::query()
            ->whereDate('date', '>=', $currentDate->toDateString())
            ->max('date');

        $summary = [
            'schedule_end_date' => $scheduleEndDate,
            'target_reminder_date' => null,
            'owners_found' => 0,
            'notifications_created' => 0,
            'emails_sent' => 0,
            'skipped' => false,
        ];

        if (!$scheduleEndDate) {
            $summary['skipped'] = true;

            return $summary;
        }

        $endDate = Carbon::parse($scheduleEndDate)->startOfDay();
        $targetReminderDate = $endDate->copy()->subDays(30);
        $summary['target_reminder_date'] = $targetReminderDate->toDateString();

        if (!$currentDate->equalTo($targetReminderDate)) {
            $summary['skipped'] = true;

            return $summary;
        }

        $owners = $this->getFacilityOwners();
        $summary['owners_found'] = $owners->count();

        if ($owners->isEmpty()) {
            $summary['skipped'] = true;

            return $summary;
        }

        $marker = $this->buildMarker($endDate, $targetReminderDate);

        foreach ($owners as $owner) {
            if ($this->wasAlreadySent($owner->id, $marker)) {
                continue;
            }

            $summary['notifications_created'] += $this->createNotification($owner, $endDate, $targetReminderDate, $marker) ? 1 : 0;
            $summary['emails_sent'] += $this->sendEmail($owner, $endDate, $targetReminderDate) ? 1 : 0;
        }

        return $summary;
    }

    protected function getFacilityOwners(): Collection
    {
        return User::with('profile')
            ->whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(title) = ?', ['owner']);
            })
            ->get();
    }

    protected function buildMarker(Carbon $endDate, Carbon $targetReminderDate): string
    {
        return 'timeslot_schedule_warning:' . $endDate->toDateString() . ':' . $targetReminderDate->toDateString();
    }

    protected function wasAlreadySent(int $userId, string $marker): bool
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->where('type', 'timeslot_schedule_expiration_warning')
            ->where('metadata->marker', $marker)
            ->exists();
    }

    protected function createNotification(User $owner, Carbon $endDate, Carbon $targetReminderDate, string $marker): bool
    {
        $notification = new Notification;
        $notification->user_id = $owner->id;
        $notification->sender_id = null;
        $notification->title = 'Timeslot Schedule Expiring Soon';
        $notification->message = 'Your timeslot schedule ends on ' . $endDate->format('m/d/Y')
            . '. Please generate more timeslots to avoid scheduling gaps.';
        $notification->type = 'timeslot_schedule_expiration_warning';
        $notification->metadata = [
            'marker' => $marker,
            'schedule_end_date' => $endDate->toDateString(),
            'target_reminder_date' => $targetReminderDate->toDateString(),
        ];
        $notification->is_read = false;
        $notification->save();

        return true;
    }

    protected function sendEmail(User $owner, Carbon $endDate, Carbon $targetReminderDate): bool
    {
        if (empty($owner->email)) {
            return false;
        }

        $recipientName = trim((($owner->profile->first_name ?? '') . ' ' . ($owner->profile->last_name ?? '')));
        $recipientName = $recipientName ?: ($owner->name ?: 'Facility Owner');

        try {
            Mail::to($owner->email)->send(new TimeSlotScheduleReminderMail([
                'subject' => 'Reminder: Timeslot schedule is running out',
                'recipient_name' => $recipientName,
                'end_date' => $endDate->format('m/d/Y'),
                'reminder_date' => $targetReminderDate->format('m/d/Y'),
                'days_remaining' => 30,
            ]));

            return true;
        } catch (\Throwable $exception) {
            Log::error('Timeslot schedule reminder email failed.', [
                'user_id' => $owner->id,
                'email' => $owner->email,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}