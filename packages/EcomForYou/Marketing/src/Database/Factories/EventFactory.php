<?php

namespace EcomForYou\Marketing\Database\Factories;

use EcomForYou\Marketing\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'name' => preg_replace('/[^a-zA-Z ]/', '', $this->faker->name()),
            'description' => substr($this->faker->paragraph, 0, 50),
            'date' => $this->faker->date,
        ];
    }
}
