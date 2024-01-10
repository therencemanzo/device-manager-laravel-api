<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Device::factory(20)->create();

        for($i = 1 ; $i <= 20 ; $i++){
            \App\Models\PhoneNumber::factory(1)->assignedToDevices()->create();
        }

        \App\Models\DeviceUserHistory::factory(20)->create();

        \App\Models\PhoneNumber::factory(10)->notAssignedToDevices()->create();

        \App\Models\User::factory(10)->create();
       

    }
}
