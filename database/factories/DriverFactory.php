<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vehicle_type' => 'moto',
            'phone' => fake()->numerify('04########'),
            'vehicle_plate' => strtoupper($this->faker->unique()->bothify('???-###')),
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ];
    }
}
