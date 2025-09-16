<?php

namespace Database\Factories;

use App\Models\TaxRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tax_region_id' => TaxRegion::factory(),
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'timezone' => $this->faker->timezone(),
            'tax_id' => $this->faker->unique()->numerify('##-#######'),
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'address_line1' => $this->faker->streetAddress(),
            'address_line2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->countryCode(),
            'default_tax_rate' => $this->faker->randomFloat(4, 0, 0.15),
            'settings' => [],
        ];
    }
}
