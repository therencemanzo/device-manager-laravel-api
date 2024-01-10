<?php

namespace Database\Factories;

use App\Models\SimCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhoneNumber>
 */
class PhoneNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->phoneNumber()
        ];
    }

    public function assignedToDevices(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'sim_card_id' => SimCard::has('device')->doesntHave('phoneNumber')->first()->id,            
            ];
        });
    }

    public function notAssignedToDevices(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'sim_card_id' => SimCard::factory()
            ];
        });
    }
}
