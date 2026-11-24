<?php

namespace EcomForYou\Checkout\Database\Factories;

use EcomForYou\Checkout\Models\CartPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CartPayment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
