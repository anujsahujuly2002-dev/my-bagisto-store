<?php

namespace EcomForYou\Core\Database\Factories;

use EcomForYou\Core\Enums\CurrencyPositionEnum;
use EcomForYou\Core\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        do {
            $currencyCode = $this->faker->unique()->currencyCode;
        } while (Currency::query()
            ->where('code', $currencyCode)
            ->exists());

        return [
            'code' => $currencyCode,
            'name' => $this->faker->word,
            'decimal' => 2,
            'group_separator' => ',',
            'decimal_separator' => '.',
            'currency_position' => CurrencyPositionEnum::LEFT->value,
        ];
    }
}
