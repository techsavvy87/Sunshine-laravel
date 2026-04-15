<?php

namespace App\Console\Commands;

use App\Services\PetVaccineExpirationMonitor;
use Illuminate\Console\Command;

class MonitorPetVaccineExpirations extends Command
{
    protected $signature = 'pets:monitor-vaccine-expiration';

    protected $description = 'Check pet vaccine expirations, notify owners and staff, and mark pets as expired when needed';

    public function handle(PetVaccineExpirationMonitor $monitor): int
    {
        $summary = $monitor->run();

        return self::SUCCESS;
    }
}