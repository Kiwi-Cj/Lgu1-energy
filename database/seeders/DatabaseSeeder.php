<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Facility::factory()->count(5)->create();
        $this->call([
            UsersTableSeeder::class,
            // NotificationSeeder::class, // Removed as requested
            // EnergyRecordSeeder::class, // Removed because it does not exist
        ]);
    }
}
