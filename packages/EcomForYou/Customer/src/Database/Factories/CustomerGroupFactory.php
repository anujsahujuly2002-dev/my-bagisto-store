<?php

namespace EcomForYou\Customer\Database\Factories;

use EcomForYou\Customer\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerGroup::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word()),
            'is_user_defined' => true,
            'code' => $this->faker->unique()->lexify('group_??????????'),
        ];
    }
}
