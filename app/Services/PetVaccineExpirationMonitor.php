<?php

namespace App\Services;

use App\Mail\PetVaccineExpirationMail;
use App\Models\Notification;
use App\Models\PetProfile;
use App\Models\PetVaccination;
use App\Models\PetVaccinationAlert;
use App\Models\User;
use Carbon\Carbon;
use Throwable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PetVaccineExpirationMonitor
{
    public function run(?Carbon $currentDate = null): array
    {
        $currentDate = ($currentDate ?: Carbon::now())->copy()->startOfDay();
        $staffUsers = $this->getStaffUsers();

        $summary = [
            'pets_checked' => 0,
            'warning_alerts_processed' => 0,
            'expired_alerts_processed' => 0,
            'pets_marked_expired' => 0,
            'pets_failed' => 0,
        ];

        PetProfile::with(['owner.profile', 'vaccinations.alerts'])
            ->chunkById(100, function (EloquentCollection $pets) use ($currentDate, $staffUsers, &$summary) {
                foreach ($pets as $pet) {
                    $summary['pets_checked']++;

                    try {
                        $result = $this->processPet($pet, $staffUsers, $currentDate);
                    } catch (Throwable $exception) {
                        $summary['pets_failed']++;

                        Log::error('Pet vaccine expiration monitor failed for pet.', [
                            'pet_id' => $pet->id,
                            'message' => $exception->getMessage(),
                        ]);

                        continue;
                    }

                    $summary['warning_alerts_processed'] += $result['warning_alerts_processed'];
                    $summary['expired_alerts_processed'] += $result['expired_alerts_processed'];
                    $summary['pets_marked_expired'] += $result['pet_marked_expired'] ? 1 : 0;
                }
            });

        return $summary;
    }

    protected function processPet(PetProfile $pet, Collection $staffUsers, Carbon $currentDate): array
    {
        $warningEntries = [];
        $expiredEntries = [];
        $hasExpiredVaccination = false;

        foreach ($pet->vaccinations as $vaccination) {
            $evaluation = $this->evaluateVaccination($vaccination, $currentDate);
            if (!$evaluation) {
                continue;
            }

            $alertType = $evaluation['status'] === PetVaccinationAlert::TYPE_EXPIRED
                ? PetVaccinationAlert::TYPE_EXPIRED
                : PetVaccinationAlert::TYPE_WARNING;

            $alert = PetVaccinationAlert::query()
                ->where('pet_vaccination_id', $vaccination->id)
                ->where('pet_profile_id', $pet->id)
                ->where('alert_type', $alertType)
                ->whereDate('expires_on', $evaluation['expires_on']->toDateString())
                ->first();

            if (!$alert) {
                $alert = new PetVaccinationAlert;
                $alert->pet_vaccination_id = $vaccination->id;
                $alert->pet_profile_id = $pet->id;
                $alert->alert_type = $alertType;
                $alert->expires_on = $evaluation['expires_on']->toDateString();
                $alert->save();
            }

            $entry = [
                'alert' => $alert,
                'vaccination' => $vaccination,
                'expires_on' => $evaluation['expires_on']->copy(),
                'days_until_expiration' => $evaluation['days_until_expiration'],
            ];

            if ($alertType === PetVaccinationAlert::TYPE_EXPIRED) {
                $hasExpiredVaccination = true;
                $expiredEntries[] = $entry;
                continue;
            }

            $warningEntries[] = $entry;
        }

        $petMarkedExpired = false;
        if ($hasExpiredVaccination && $pet->vaccine_status !== PetProfile::VACCINE_STATUS_EXPIRED) {
            $pet->vaccine_status = PetProfile::VACCINE_STATUS_EXPIRED;
            $pet->save();
            $petMarkedExpired = true;
        } elseif (!$hasExpiredVaccination && !empty($warningEntries) && $pet->vaccine_status === PetProfile::VACCINE_STATUS_EXPIRED) {
            $pet->vaccine_status = 'approved';
            $pet->save();
        }

        $warningAlertsProcessed = $this->dispatchAlerts($pet, $staffUsers, PetVaccinationAlert::TYPE_WARNING, $warningEntries);
        $expiredAlertsProcessed = $this->dispatchAlerts($pet, $staffUsers, PetVaccinationAlert::TYPE_EXPIRED, $expiredEntries);

        return [
            'warning_alerts_processed' => $warningAlertsProcessed,
            'expired_alerts_processed' => $expiredAlertsProcessed,
            'pet_marked_expired' => $petMarkedExpired,
        ];
    }

    protected function evaluateVaccination(PetVaccination $vaccination, Carbon $currentDate): ?array
    {
        if (!$vaccination->date || empty($vaccination->months)) {
            return null;
        }

        $expiresOn = Carbon::parse($vaccination->date)->startOfDay()->addMonthsNoOverflow((int) $vaccination->months);
        $warningThreshold = $expiresOn->copy()->subMonthNoOverflow();

        if ($currentDate->greaterThanOrEqualTo($expiresOn)) {
            return [
                'status' => PetVaccinationAlert::TYPE_EXPIRED,
                'expires_on' => $expiresOn,
                'days_until_expiration' => 0,
            ];
        }

        if ($currentDate->greaterThanOrEqualTo($warningThreshold)) {
            return [
                'status' => PetVaccinationAlert::TYPE_WARNING,
                'expires_on' => $expiresOn,
                'days_until_expiration' => $currentDate->diffInDays($expiresOn, false),
            ];
        }

        return null;
    }

    protected function dispatchAlerts(PetProfile $pet, Collection $staffUsers, string $alertType, array $entries): int
    {
        if (empty($entries)) {
            return 0;
        }

        $pendingEmailEntries = array_values(array_filter($entries, function (array $entry) {
            return $entry['alert']->email_sent_at === null;
        }));

        $pendingNotificationEntries = array_values(array_filter($entries, function (array $entry) {
            return $entry['alert']->in_app_sent_at === null;
        }));

        if (empty($pendingEmailEntries) && empty($pendingNotificationEntries)) {
            return 0;
        }

        $recipients = $this->getRecipients($pet, $staffUsers);

        if (!empty($pendingEmailEntries)) {
            $emailSent = $this->sendEmails($pet, $alertType, $pendingEmailEntries, $recipients);

            if ($emailSent) {
                $this->markAlertsAsSent($pendingEmailEntries, 'email_sent_at');
            }
        }

        if (!empty($pendingNotificationEntries)) {
            $notificationsSent = $this->createNotifications($pet, $alertType, $pendingNotificationEntries, $recipients);

            if ($notificationsSent) {
                $this->markAlertsAsSent($pendingNotificationEntries, 'in_app_sent_at');
            }
        }

        return count($entries);
    }

    protected function sendEmails(PetProfile $pet, string $alertType, array $entries, Collection $recipients): bool
    {
        $emailRecipients = $recipients->filter(function (User $user) {
            return !empty($user->email);
        });

        if ($emailRecipients->isEmpty()) {
            Log::warning('Pet vaccine expiration monitor found no email recipients.', [
                'pet_id' => $pet->id,
                'alert_type' => $alertType,
            ]);

            return false;
        }

        $allSucceeded = true;

        foreach ($emailRecipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new PetVaccineExpirationMail(
                    $this->buildMailData($pet, $alertType, $entries, $recipient)
                ));
            } catch (Throwable $exception) {
                $allSucceeded = false;

                Log::error('Pet vaccine expiration email failed.', [
                    'pet_id' => $pet->id,
                    'recipient_id' => $recipient->id,
                    'recipient_email' => $recipient->email,
                    'alert_type' => $alertType,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $allSucceeded;
    }

    protected function createNotifications(PetProfile $pet, string $alertType, array $entries, Collection $recipients): bool
    {
        if ($recipients->isEmpty()) {
            Log::warning('Pet vaccine expiration monitor found no notification recipients.', [
                'pet_id' => $pet->id,
                'alert_type' => $alertType,
            ]);

            return false;
        }

        $title = $alertType === PetVaccinationAlert::TYPE_EXPIRED
            ? 'Pet Vaccine Expired'
            : 'Pet Vaccine Expiration Warning';

        $message = $this->buildNotificationMessage($pet, $alertType, $entries);
        $metadata = [
            'pet_id' => $pet->id,
            'vaccination_ids' => array_values(array_unique(array_map(function (array $entry) {
                return $entry['vaccination']->id;
            }, $entries))),
            'alert_type' => $alertType,
            'expires_on' => array_values(array_unique(array_map(function (array $entry) {
                return $entry['expires_on']->toDateString();
            }, $entries))),
        ];

        $createdAny = false;

        foreach ($recipients as $recipient) {
            $notification = new Notification;
            $notification->user_id = $recipient->id;
            $notification->sender_id = null;
            $notification->title = $title;
            $notification->message = $message;
            $notification->type = $alertType === PetVaccinationAlert::TYPE_EXPIRED
                ? 'pet_vaccine_expired'
                : 'pet_vaccine_expiration_warning';
            $notification->metadata = $metadata;
            $notification->is_read = false;
            $notification->save();
            $createdAny = true;
        }

        return $createdAny;
    }

    protected function markAlertsAsSent(array $entries, string $column): void
    {
        $timestamp = now();

        foreach ($entries as $entry) {
            $alert = $entry['alert'];
            $alert->{$column} = $timestamp;
            $alert->save();
        }
    }

    protected function getStaffUsers(): Collection
    {
        return User::with('profile')
            ->whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(title) <> ?', ['customer']);
            })
            ->get();
    }

    protected function getRecipients(PetProfile $pet, Collection $staffUsers): Collection
    {
        $recipients = collect();

        if ($pet->owner) {
            $recipients->push($pet->owner);
        }

        foreach ($staffUsers as $staffUser) {
            $recipients->push($staffUser);
        }

        return $recipients->unique('id')->values();
    }

    protected function buildMailData(PetProfile $pet, string $alertType, array $entries, User $recipient): array
    {
        $recipientName = trim((($recipient->profile->first_name ?? '') . ' ' . ($recipient->profile->last_name ?? '')));
        $recipientName = $recipientName ?: ($recipient->name ?: 'PawPrints customer');

        $subject = $alertType === PetVaccinationAlert::TYPE_EXPIRED
            ? 'Urgent: Pet vaccine expired'
            : 'Reminder: Pet vaccine expiring soon';

        return [
            'subject' => $subject,
            'recipient_name' => $recipientName,
            'pet_name' => $pet->name,
            'owner_name' => optional($pet->owner)->name,
            'alert_type' => $alertType,
            'vaccines' => array_map(function (array $entry) {
                return [
                    'type' => ucfirst($entry['vaccination']->type),
                    'date' => Carbon::parse($entry['vaccination']->date)->format('m/d/Y'),
                    'expires_on' => $entry['expires_on']->format('m/d/Y'),
                    'months' => $entry['vaccination']->months,
                    'days_until_expiration' => $entry['days_until_expiration'],
                ];
            }, $entries),
        ];
    }

    protected function buildNotificationMessage(PetProfile $pet, string $alertType, array $entries): string
    {
        $vaccines = implode(', ', array_map(function (array $entry) {
            return ucfirst($entry['vaccination']->type) . ' (' . $entry['expires_on']->format('m/d/Y') . ')';
        }, $entries));

        if ($alertType === PetVaccinationAlert::TYPE_EXPIRED) {
            return 'Pet ' . $pet->name . ' has expired vaccines: ' . $vaccines . '.';
        }

        return 'Pet ' . $pet->name . ' has vaccines expiring within 1 month: ' . $vaccines . '.';
    }
}