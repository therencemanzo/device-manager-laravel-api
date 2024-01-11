<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Device::factory(20)->create();
        \App\Models\User::factory(20)->create();

        foreach(Device::all() as $device){
            $user = \App\Models\User::inRandomOrder()->take(rand(1,3))->pluck('id');
            $device->users()->attach($user);
        }

        for($i = 1 ; $i <= 20 ; $i++){
            \App\Models\PhoneNumber::factory(1)->assignedToDevices()->create();
        }
        

        \App\Models\PhoneNumber::factory(10)->notAssignedToDevices()->create();       

    }
}
