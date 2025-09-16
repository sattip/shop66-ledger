<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxRegion>
 */
class TaxRegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country(),
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'country_code' => $this->faker->countryCode(),
            'region' => $this->faker->optional()->state(),
            'default_rate' => $this->faker->randomFloat(4, 0, 0.15),
            'settings' => [],
        ];
    }
}
