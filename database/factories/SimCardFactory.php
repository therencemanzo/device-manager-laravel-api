<?php

namespace Database\Factories;

use App\Models\NetworkProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SimCard>
 */
class SimCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'network_provider_id' => NetworkProvider::factory(),
            'name' =>   fake()->randomElement(array: ['EE', 'Vodafone', 'Globe', 'Smart']).'-'.fake()->randomElement(array: ['200GB', '100GB', '50GB', '25GB']),
            'serial' => fake()->unique()->randomNumber(7, true),
        ];
    }
}
