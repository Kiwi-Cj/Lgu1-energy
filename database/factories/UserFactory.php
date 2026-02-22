<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        $name = fake()->name();
        $email = fake()->unique()->safeEmail();

        return [
            'facility_id' => null,
            'full_name' => $name,
            'name' => $name,
            'email' => $email,
            'username' => Str::before($email, '@') . fake()->numberBetween(10, 99),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
            'contact_number' => '09123456789',
            'department' => 'General',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
