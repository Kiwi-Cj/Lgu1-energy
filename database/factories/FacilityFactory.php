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
            'floor_area' => $this->faker->numberBetween(100, 10000),
            'status' => 'active',
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
