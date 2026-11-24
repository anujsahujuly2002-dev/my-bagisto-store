<?php

namespace EcomForYou\Tax\Database\Factories;

use EcomForYou\Tax\Models\TaxMap;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxMapFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaxMap::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
