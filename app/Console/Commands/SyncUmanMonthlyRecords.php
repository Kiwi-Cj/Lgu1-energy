<?php

namespace App\Console\Commands;

use App\Services\UmanMonthlyRecordSyncService;
use Illuminate\Console\Command;

class SyncUmanMonthlyRecords extends Command
{
    protected $signature = 'energy:sync-uman-monthly-records {--year=} {--month=}';

    protected $description = 'Pull CPRF-originated monthly records from UMAN into matching Energy facilities';

    public function handle(UmanMonthlyRecordSyncService $service): int
    {
        $year = $this->option('year') !== null ? (int) $this->option('year') : null;
        $month = $this->option('month') !== null ? (int) $this->option('month') : null;

        if ($month !== null && ($month < 1 || $month > 12)) {
            $this->error('The --month option must be between 1 and 12.');

            return self::INVALID;
        }

        $result = $service->sync($year, $month);
        if (! $result['success']) {
            $this->error('Sync failed: '.($result['error'] ?? 'unknown error'));

            return self::FAILURE;
        }

        $this->info(sprintf(
            'UMAN monthly records synced: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
