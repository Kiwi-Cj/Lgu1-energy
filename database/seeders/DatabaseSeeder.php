<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Facility::factory()->count(5)->create();
        $this->call([
            UserSeeder::class,
            EnergyRecordSeeder::class,
        ]);
    }
}
