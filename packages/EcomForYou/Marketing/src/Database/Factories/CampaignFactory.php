<?php

namespace EcomForYou\Marketing\Database\Factories;

use EcomForYou\Marketing\Models\Campaign;
use EcomForYou\Marketing\Models\Event;
use EcomForYou\Marketing\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Campaign::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'subject' => $this->faker->sentence(),
            'status' => true,
            'marketing_template_id' => Template::factory(),
            'marketing_event_id' => Event::factory(),
            'channel_id' => core()->getCurrentChannel()->id,
            'customer_group_id' => 1,
        ];
    }
}
