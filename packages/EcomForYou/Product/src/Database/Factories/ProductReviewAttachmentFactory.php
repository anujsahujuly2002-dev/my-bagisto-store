<?php

namespace EcomForYou\Product\Database\Factories;

use EcomForYou\Product\Models\ProductReviewAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductReviewAttachmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductReviewAttachment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
