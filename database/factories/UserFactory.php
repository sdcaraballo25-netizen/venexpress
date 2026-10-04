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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            // Código de verificación de VenExpress ya confirmado
            // (EnsureAccountIsVerified). Ver unverifiedAccount().
            'account_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'account_verified_at' => null,
        ]);
    }

    /**
     * Cuenta autorregistrada que todavía no introdujo el código de
     * verificación de 6 dígitos.
     */
    public function unverifiedAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_verified_at' => null,
        ]);
    }
}
