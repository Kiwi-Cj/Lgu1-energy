<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:check-consumptions', function () {
    $rows = DB::table('consumptions')
        ->select('facility_id', 'month', 'kwh')
        ->orderBy('facility_id')
        ->orderBy('month')
        ->get();
    if ($rows->isEmpty()) {
        $this->info('No data found in consumptions table.');
        return;
    }
    $this->info("facility_id | month     | kwh");
    foreach ($rows as $row) {
        $this->line(sprintf("%11d | %s | %.2f", $row->facility_id, $row->month, $row->kwh));
    }
})->describe('Check consumptions table for demo data');
