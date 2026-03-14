<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            FiveFacilitiesEnergySeeder::class,
            DualMainWithSubMonthlySeeder::class,
            TenFacilitiesMultiMeterMonthlySeeder::class,
            SubmeterEquipmentSeeder::class,
        ]);
    }
}
