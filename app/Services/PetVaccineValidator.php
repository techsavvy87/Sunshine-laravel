<?php

namespace App\Services;

use App\Models\PetProfile;
use App\Models\PetVaccination;
use Carbon\Carbon;

class PetVaccineValidator
{
    /**
     * Required vaccines per pet type (case-insensitive match against vaccination.type).
     */
    const REQUIRED_VACCINES = [
        'dog' => ['DHPP', 'Bordetella', 'Rabies'],
        'cat' => ['FVRCP', 'Rabies'],
    ];

    /**
     * Validate that all required vaccines for a pet are present and not expired.
     *
     * Returns:
     *   ['valid' => true]
    *   ['valid' => false, 'status' => 'missing'|'expired', 'vaccine' => string|null, 'message' => string, 'messages' => string[]]
     */
    public function validate(PetProfile $pet): array
    {
        $petType = strtolower((string) $pet->type);
        $required = self::REQUIRED_VACCINES[$petType] ?? null;

        // Unknown pet type – fall back to the legacy vaccine_status field
        if ($required === null) {
            return $this->fallbackToStatus($pet);
        }

        $vaccinations = $pet->vaccinations; // lazy-loads if not already loaded
        $today = Carbon::today();
        $missingVaccines = [];
        $expiredVaccines = [];

        foreach ($required as $vaccineType) {
            $record = $this->findLatestRecord($vaccinations, $vaccineType);

            if (!$record) {
                $missingVaccines[] = $vaccineType;
                continue;
            }

            if ($this->isExpired($record, $today)) {
                $expiredVaccines[] = $vaccineType;
            }
        }

        if (!empty($missingVaccines) || !empty($expiredVaccines)) {
            $status = !empty($missingVaccines) ? 'missing' : 'expired';
            $primaryVaccine = !empty($missingVaccines) ? $missingVaccines[0] : $expiredVaccines[0];
            $messages = $this->buildDetailedMessages($missingVaccines, $expiredVaccines);

            return [
                'valid'   => false,
                'status'  => $status,
                'vaccine' => $primaryVaccine,
                'message' => $this->buildSummaryMessage($missingVaccines, $expiredVaccines),
                'messages' => $messages,
            ];
        }

        return ['valid' => true];
    }

    private function buildSummaryMessage(array $missingVaccines, array $expiredVaccines): string
    {
        $parts = [];

        if (!empty($missingVaccines)) {
            $parts[] = $this->formatVaccineList($missingVaccines) . ' vaccination' . (count($missingVaccines) > 1 ? 's are' : ' is') . ' missing.';
        }

        if (!empty($expiredVaccines)) {
            $parts[] = $this->formatVaccineList($expiredVaccines) . ' vaccination' . (count($expiredVaccines) > 1 ? 's are' : ' is') . ' expired.';
        }

        return implode(' ', $parts);
    }

    private function buildDetailedMessages(array $missingVaccines, array $expiredVaccines): array
    {
        $messages = [];

        foreach ($missingVaccines as $vaccine) {
            $messages[] = $vaccine . ' vaccination is missing.';
        }

        foreach ($expiredVaccines as $vaccine) {
            $messages[] = $vaccine . ' vaccination is expired.';
        }

        return $messages;
    }

    private function formatVaccineList(array $vaccines): string
    {
        $vaccines = array_values($vaccines);
        $count = count($vaccines);

        if ($count <= 1) {
            return $vaccines[0] ?? '';
        }

        if ($count === 2) {
            return $vaccines[0] . ' and ' . $vaccines[1];
        }

        $last = array_pop($vaccines);

        return implode(', ', $vaccines) . ', and ' . $last;
    }

    /**
     * Find the most recent vaccination record matching the given type (case-insensitive).
     */
    private function findLatestRecord($vaccinations, string $type): ?PetVaccination
    {
        return $vaccinations
            ->filter(fn ($v) => strcasecmp((string) $v->type, $type) === 0)
            ->sortByDesc(fn ($v) => optional($v->date)->timestamp ?? 0)
            ->first();
    }

    /**
     * A vaccination record is expired when today >= (vaccination date + months).
     * If no date or months are recorded the record is treated as valid (no expiry data).
     */
    private function isExpired(PetVaccination $record, Carbon $today): bool
    {
        if (!$record->date || empty($record->months)) {
            return false;
        }

        $expiresOn = $record->date->copy()->startOfDay()->addMonthsNoOverflow((int) $record->months);

        return $today->greaterThanOrEqualTo($expiresOn);
    }

    /**
     * Legacy fallback for pet types not covered by REQUIRED_VACCINES.
     */
    private function fallbackToStatus(PetProfile $pet): array
    {
        if ($pet->vaccine_status === 'approved') {
            return ['valid' => true];
        }

        if ($pet->vaccine_status === 'expired') {
            return [
                'valid'   => false,
                'status'  => 'expired',
                'vaccine' => null,
                'message' => 'Pet vaccination is expired.',
                'messages' => ['Pet vaccination is expired.'],
            ];
        }

        return [
            'valid'   => false,
            'status'  => 'missing',
            'vaccine' => null,
            'message' => 'Pet vaccination records are not approved.',
            'messages' => ['Pet vaccination records are not approved.'],
        ];
    }
}
