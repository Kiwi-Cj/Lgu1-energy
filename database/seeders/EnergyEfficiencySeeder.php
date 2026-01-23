<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnergyEfficiencySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('energy_efficiency')->insert([
            [
                'facility' => 'City Hall',
                'month' => 'Jan',
                'year' => 2026,
                'actual_kwh' => 15200,
                'avg_kwh' => 13000,
                'variance' => 2200,
                'eui' => 12.5,
                'rating' => 'Low',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'facility' => 'City Hall',
                'month' => 'Feb',
                'year' => 2026,
                'actual_kwh' => 14500,
                'avg_kwh' => 13000,
                'variance' => 1500,
                'eui' => 11.9,
                'rating' => 'Low',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'facility' => 'City Hall',
                'month' => 'Mar',
                'year' => 2026,
                'actual_kwh' => 12800,
                'avg_kwh' => 13000,
                'variance' => -200,
                'eui' => 10.5,
                'rating' => 'High',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more rows as needed
        ]);
    }
}
