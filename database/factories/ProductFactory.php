<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));

        $supplierIds = Supplier::pluck('id');

        return [
            'name' => $faker->productName,
            'description' => $faker->sentence(),
            'price' => $faker->randomFloat(2, 1, 100),
            'supplier_id' => $faker->randomElement($supplierIds)
        ];
    }
}
