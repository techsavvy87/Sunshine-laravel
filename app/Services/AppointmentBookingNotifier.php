<?php

namespace App\Services;

use App\Mail\AdminCustomerMessage;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentBookingNotifier
{
    public function sendConfirmation(Appointment $appointment, ?int $senderId = null): bool
    {
        return $this->dispatch($appointment, 'booking_confirmation', [
            'marker' => 'confirmation',
            'owner_email_subject' => 'Booking Confirmed',
            'owner_notification_title' => 'Booking Confirmed',
            'staff_notification_title' => 'New Booking Confirmed',
            'owner_email_message' => $this->buildConfirmationMessage($appointment, true),
            'owner_notification_message' => $this->buildConfirmationMessage($appointment, false),
            'staff_notification_message' => $this->buildStaffConfirmationMessage($appointment),
        ], $senderId);
    }

    public function sendCancellation(Appointment $appointment, ?int $senderId = null): bool
    {
        return $this->dispatch($appointment, 'booking_cancellation', [
            'marker' => 'cancellation:' . Carbon::now()->timestamp,
            'owner_email_subject' => 'Booking Cancelled',
            'owner_notification_title' => 'Booking Cancelled',
            'staff_notification_title' => 'Booking Cancelled',
            'owner_email_message' => $this->buildCancellationMessage($appointment, true),
            'owner_notification_message' => $this->buildCancellationMessage($appointment, false),
            'staff_notification_message' => $this->buildStaffCancellationMessage($appointment),
        ], $senderId);
    }

    public function sendReminder(Appointment $appointment, ?Carbon $reminderDate = null, ?int $senderId = null): bool
    {
        $reminderDate = ($reminderDate ?: Carbon::tomorrow())->copy()->startOfDay();

        return $this->dispatch($appointment, 'booking_reminder', [
            'marker' => 'reminder:' . $reminderDate->toDateString(),
            'owner_email_subject' => 'Booking Reminder',
            'owner_notification_title' => 'Booking Reminder',
            'staff_notification_title' => 'Upcoming Boarding Reminder',
            'owner_email_message' => $this->buildReminderMessage($appointment, true),
            'owner_notification_message' => $this->buildReminderMessage($appointment, false),
            'staff_notification_message' => $this->buildStaffReminderMessage($appointment),
        ], $senderId);
    }

    protected function dispatch(Appointment $appointment, string $eventType, array $content, ?int $senderId = null): bool
    {
        $appointment->loadMissing([
            'customer.profile',
            'pet',
            'staff.profile',
            'service.category',
            'kennel',
            'catRoom',
        ]);

        $marker = $content['marker'];
        if ($this->hasMarker($appointment, $marker)) {
            return false;
        }

        $notificationSent = false;

        // Send notification to customer
        if ($appointment->customer_id) {
            $notificationSent = $this->createNotification(
                $appointment->customer_id,
                $senderId,
                $content['owner_notification_title'],
                $content['owner_notification_message'],
                $eventType,
                $appointment,
                $marker
            ) || $notificationSent;

            if (!empty($appointment->customer?->email)) {
                $this->sendOwnerEmail($appointment, $content['owner_email_subject'], $content['owner_email_message']);
            }
        }

        // Send notification to all facility staff (not just assigned staff)
        $staffUsers = $this->getAllStaffUsers();
        foreach ($staffUsers as $staff) {
            // Skip if staff is the customer
            if ($staff->id === $appointment->customer_id) {
                continue;
            }

            $notificationSent = $this->createNotification(
                $staff->id,
                $senderId,
                $content['staff_notification_title'],
                $content['staff_notification_message'],
                $eventType,
                $appointment,
                $marker
            ) || $notificationSent;
        }

        if ($notificationSent) {
            $this->markMarker($appointment, $marker);
        }

        return $notificationSent;
    }

    protected function createNotification(
        int $userId,
        ?int $senderId,
        string $title,
        string $message,
        string $eventType,
        Appointment $appointment,
        string $marker
    ): bool {
        $notification = new Notification;
        $notification->user_id = $userId;
        // For system notifications like booking confirmations/reminders, sender_id should be null
        // These are not messages from a specific person, but system-generated notifications
        $notification->sender_id = null;
        $notification->title = $title;
        $notification->message = $message;
        $notification->type = $eventType;
        $notification->metadata = [
            'appointment_id' => $appointment->id,
            'marker' => $marker,
            'event_type' => $eventType,
            'service_id' => $appointment->service_id,
        ];
        $notification->is_read = false;
        $notification->save();

        return true;
    }

    protected function sendOwnerEmail(Appointment $appointment, string $subject, string $message): void
    {
        try {
            Mail::to($appointment->customer->email)->send(new AdminCustomerMessage([
                'subject' => $subject,
                'customer_name' => $this->resolveCustomerName($appointment),
                'message' => $message,
                'sender_name' => 'Sunshine Spot Team',
            ]));
        } catch (\Throwable $exception) {
            Log::error('Appointment booking email failed.', [
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'email' => $appointment->customer->email,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function hasMarker(Appointment $appointment, string $marker): bool
    {
        $metadata = is_array($appointment->metadata) ? $appointment->metadata : [];
        $markers = $metadata['booking_notification_markers'] ?? [];

        if (is_string($markers)) {
            $markers = explode(',', $markers);
        }

        return collect(is_array($markers) ? $markers : [])->contains($marker);
    }

    protected function markMarker(Appointment $appointment, string $marker): void
    {
        $metadata = is_array($appointment->metadata) ? $appointment->metadata : [];
        $markers = $metadata['booking_notification_markers'] ?? [];

        if (is_string($markers)) {
            $markers = explode(',', $markers);
        }

        $metadata['booking_notification_markers'] = collect(is_array($markers) ? $markers : [])
            ->push($marker)
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->values()
            ->all();

        $appointment->metadata = $metadata;
        $appointment->save();
    }

    protected function getAllStaffUsers()
    {
        return User::with('profile')
            ->whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(title) <> ?', ['customer']);
            })
            ->get();
    }

    protected function buildConfirmationMessage(Appointment $appointment, bool $forEmail): string
    {
        $petName = $this->resolvePetName($appointment);
        $lines = [
            'Hi ' . $this->resolveCustomerFirstName($appointment) . ',',
            '',
            'Your ' . strtolower($appointment->service->name ?? 'booking') . ' reservation for ' . $petName . ' has been confirmed.',
            '',
            'Check-in: ' . $this->formatDate($appointment->date),
        ];

        if (!empty($appointment->end_date)) {
            $lines[] = 'Pickup: ' . $this->formatDate($appointment->end_date);
        }

        if ($location = $this->resolveLocationLabel($appointment)) {
            $lines[] = $location['label'] . ': ' . $location['value'];
        }

        if ($forEmail) {
            $lines[] = '';
            $lines[] = 'Thank you.';
        }

        return implode("\n", $lines);
    }

    protected function buildStaffConfirmationMessage(Appointment $appointment): string
    {
        return $this->resolvePetName($appointment) . ' has a confirmed ' . strtolower($appointment->service->name ?? 'booking')
            . ' for ' . $this->formatDate($appointment->date) . '.';
    }

    protected function buildReminderMessage(Appointment $appointment, bool $forEmail): string
    {
        $petName = $this->resolvePetName($appointment);
        $startAt = $this->formatDateTime($appointment->date, $appointment->start_time);

        $lines = [
            'Hi ' . $this->resolveCustomerFirstName($appointment) . ',',
            '',
            'Reminder:',
            $petName . "'s boarding stay starts tomorrow" . ($startAt ? ' at ' . $startAt . '.' : '.'),
            'Please bring food, medication, and vaccination records if needed.',
        ];

        if ($forEmail) {
            $lines[] = '';
            $lines[] = 'Thank you.';
        }

        return implode("\n", $lines);
    }

    protected function buildStaffReminderMessage(Appointment $appointment): string
    {
        return 'Reminder: ' . $this->resolvePetName($appointment) . ' starts boarding tomorrow'
            . ($appointment->start_time ? ' at ' . $this->formatTime($appointment->start_time) : '') . '.';
    }

    protected function buildCancellationMessage(Appointment $appointment, bool $forEmail): string
    {
        $lines = [
            'Hi ' . $this->resolveCustomerFirstName($appointment) . ',',
            '',
            'Your reservation for ' . $this->resolvePetName($appointment) . ' on ' . $this->formatDate($appointment->date) . ' has been cancelled.',
            'If this was unexpected, contact us.',
        ];

        if ($forEmail) {
            $lines[] = '';
            $lines[] = 'Thank you.';
        }

        return implode("\n", $lines);
    }

    protected function buildStaffCancellationMessage(Appointment $appointment): string
    {
        return $this->resolvePetName($appointment) . ' has a cancelled booking for ' . $this->formatDate($appointment->date) . '.';
    }

    protected function resolveCustomerName(Appointment $appointment): string
    {
        $firstName = $appointment->customer?->profile?->first_name ?? '';
        $lastName = $appointment->customer?->profile?->last_name ?? '';
        $fullName = trim($firstName . ' ' . $lastName);

        return $fullName !== '' ? $fullName : ($appointment->customer?->name ?? 'Customer');
    }

    protected function resolveCustomerFirstName(Appointment $appointment): string
    {
        $firstName = trim((string) ($appointment->customer?->profile?->first_name ?? ''));

        return $firstName !== '' ? $firstName : $this->resolveCustomerName($appointment);
    }

    protected function resolvePetName(Appointment $appointment): string
    {
        $petNames = $appointment->family_pets->pluck('name')->filter()->values();

        if ($petNames->isNotEmpty()) {
            return $petNames->implode(', ');
        }

        return $appointment->pet?->name ?? 'your pet';
    }

    protected function resolveLocationLabel(Appointment $appointment): ?array
    {
        if (!empty($appointment->kennel?->name)) {
            return ['label' => 'Kennel', 'value' => $appointment->kennel->name];
        }

        if (!empty($appointment->catRoom?->name)) {
            return ['label' => 'Room', 'value' => $appointment->catRoom->name];
        }

        return null;
    }

    protected function formatDate(?string $date): string
    {
        if (!$date) {
            return 'N/A';
        }

        return Carbon::parse($date)->format('M j, Y');
    }

    protected function formatTime(?string $time): string
    {
        if (!$time) {
            return '';
        }

        return Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
    }

    protected function formatDateTime(?string $date, ?string $time): string
    {
        if (!$date) {
            return '';
        }

        $dateTime = Carbon::parse($date . ' ' . ($time ?: '00:00:00'));

        return $dateTime->format('g:i A');
    }
}