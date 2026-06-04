<?php

namespace App\Observers;

use App\Models\EnergyIncident;
use App\Services\IncidentNotificationService;

class EnergyIncidentObserver
{
    public function created(EnergyIncident $incident): void
    {
        try {
            app(IncidentNotificationService::class)->notify($incident);
        } catch (\Throwable) {
            // Do not break incident creation flow if notification creation fails.
        }
    }
}
