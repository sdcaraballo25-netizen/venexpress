<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => 'Almacén '.fake()->city(),
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'address' => fake()->streetAddress(),
            'is_active' => true,
        ];
    }
}
