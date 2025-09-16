<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Office Supplies', 'Food & Beverage', 'Equipment', 
            'Software', 'Marketing', 'Travel', 'Utilities',
            'Professional Services', 'Maintenance', 'Inventory'
        ]);
        
        return [
            'store_id' => Store::factory(),
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => $this->faker->randomElement(['expense', 'income']),
            'description' => $this->faker->sentence(),
            'is_system' => false,
            'is_active' => true,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
