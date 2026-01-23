<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\EnergyRecord;
use App\Observers\EnergyRecordObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EnergyRecord::observe(EnergyRecordObserver::class);
    }
}
