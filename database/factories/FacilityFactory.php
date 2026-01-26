<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company . ' Facility',
            'type' => $this->faker->randomElement(['Office', 'Warehouse', 'Plant']),
            'size' => $this->faker->randomElement(['small', 'medium', 'large', 'extralarge']),
            'department' => $this->faker->word,
            'address' => $this->faker->address,
            'barangay' => $this->faker->word,
            'floor_area' => $this->faker->numberBetween(100, 10000),
            'floors' => $this->faker->numberBetween(1, 10),
            'year_built' => $this->faker->year,
            'operating_hours' => $this->faker->numberBetween(8, 24),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'image' => null,
        ];
    }
}
