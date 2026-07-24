<?php

namespace App\Console\Commands;

use App\Services\CprfFacilitySyncService;
use Illuminate\Console\Command;

class SyncCprfFacilities extends Command
{
    protected $signature = 'energy:sync-cprf-facilities';

    protected $description = 'Mirror public facilities from the CPRF facilities reservation feed (source=cprf, identity read-only)';

    public function handle(CprfFacilitySyncService $service): int
    {
        $result = $service->sync();

        if (! $result['success']) {
            $this->error('Sync failed: ' . ($result['error'] ?? 'unknown error'));

            return self::FAILURE;
        }

        $this->info(sprintf(
            'CPRF facilities synced: %d created, %d updated, %d deactivated, %d unchanged.',
            $result['created'],
            $result['updated'],
            $result['deactivated'],
            $result['unchanged']
        ));

        return self::SUCCESS;
    }
}
