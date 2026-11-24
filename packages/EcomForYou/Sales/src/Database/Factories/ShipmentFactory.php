<?php

namespace EcomForYou\Sales\Database\Factories;

use EcomForYou\Sales\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shipment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'total_qty' => $this->faker->numberBetween(1, 20),
        ];
    }
}
