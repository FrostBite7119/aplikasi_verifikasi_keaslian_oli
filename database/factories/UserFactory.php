<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 'USR-' . strtoupper(Str::random(8)),
            'name' => fake()->name(),
            'phone_number' => fake()->unique()->numerify('08##########'),
            'bank' => fake()->randomElement(['BCA', 'BNI', 'BRI', 'Mandiri']),
            'bank_account_number' => fake()->unique()->numerify('##########'),
            'account_type' => fake()->randomElement(['store', 'mechanic']),
            'validation_status' => 'approved',
            'validation_date' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is pending validation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_status' => 'pending',
            'validation_date' => null,
        ]);
    }
}
