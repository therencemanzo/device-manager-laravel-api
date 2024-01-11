<?php

namespace Database\Factories;

use App\Models\SimCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sim_card_id' => SimCard::factory(),
            'serial_number' => fake()->randomNumber(6, true).Str::random(10),
            'imei' => Str::random(6).fake()->randomNumber(4, true),
            'model' =>  fake()->randomElement(['Apple', 'Samsung', 'Sony', 'Panasonic']).'-'. fake()->randomNumber(6, true).Str::random(6),
            'manufacturer' =>  fake()->randomElement(['Apple', 'Samsung', 'Sony', 'Panasonic']),
            'operating_system' => fake()->randomElement(['ios', 'andriod']),
            'type' => fake()->randomElement(array: ['mobile', 'tablet']),
        ];
    }
}
