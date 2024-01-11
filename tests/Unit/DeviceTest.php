<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use App\Console\Kernel;
use App\Jobs\ProcessDeviceExport;
use App\Jobs\PruningDeactivatedDevices;
use Artisan;
use Bus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Queue;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;
use App\Models\User;
use App\Models\SimCard;
use App\Models\Device;
use App\Models\DeviceUserHistory;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;

class DeviceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_accessing_device_api_resource_without_credentials(): void
    {
        $response = $this->postJson('/api/v1/devices');
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => true
            ]);
    }

    public function test_listing_all_devices_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $response = $this->getJson('/api/v1/devices');
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->hasAll(['data', 'links', 'meta'])
            );
    }

    public function test_add_devices_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::doesntHave('device')->first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(201);
    }

    public function test_add_devices_with_no_required_feilds_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::doesntHave('device')->first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => "",
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_duplicate_sim_feilds_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::has('device')->first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_duplicate_serial_number_feilds_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::doesntHave('device')->first();
        $device = Device::first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => $device->serial_number,
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_duplicate_imei_feilds_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::doesntHave('device')->first();
        $device = Device::first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => Str::random(10),
            "imei" => $device->imei,
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_invalid_operating_system_feild_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::doesntHave('device')->first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => 'windows',
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_invalid_type_feild_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        $simcard = SimCard::doesntHave('device')->first();
        $device = Device::first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $simcard->id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => 'pc',
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_user_not_exist_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::orderBy('id', 'desc')->first();
        $simcard = SimCard::doesntHave('device')->first();
        $device = Device::first();

        $data = [
            "user_id" => intval($user->id) + 1,
            "sim_card_id" => $simcard->id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => 'pc',
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_add_devices_with_sim_card_not_exist_return_error_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );
        $user = User::first();
        User::orderBy('id', 'desc')->first();

        $simcard = SimCard::orderBy('id', 'desc')->first();

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => intval($simcard->id) + 1,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(['ios', 'andriod']),
            "type" => 'pc',
            "is_active" => 1
        ];

        $response = $this->postJson('/api/v1/devices', $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => true
            ]);
    }

    public function test_show_device_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $response = $this->getJson($url);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
    }

    public function test_show_device_not_exist_with_errors_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . intval($device->id) + 100;



        $response = $this->getJson($url);
        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => true,
            ]);
    }

    public function test_update_device_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $data = [
            "user_id" => $device->latestUser[0]->id,
            "sim_card_id" => $device->sim_card_id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->putJson($url, $data);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
    }

    public function test_update_device_check_column_data_updated_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $serial_number = Str::random(10);

        $data = [
            "user_id" => $device->latestUser[0]->id,
            "sim_card_id" => $device->sim_card_id,
            "serial_number" => $serial_number,
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->putJson($url, $data);
        $response
        ->assertStatus(200)
        ->assertJsonFragment(['serial_number' => $serial_number]);
    }

    public function test_update_device_status_check_column_update_status_date_updated_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $serial_number = Str::random(10);

        $data = [
            "user_id" => $device->latestUser[0]->id,
            "sim_card_id" => $device->sim_card_id,
            "serial_number" => $device->serial_number,
            "imei" => $device->imei,
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 0
        ];

        $response = $this->putJson($url, $data);
        $response
        ->assertStatus(200);

        $device = Device::find($device->id);
        $update_time = date('Y-m-d h:i', strtotime($device->status_update_at));
        $this->assertEquals(date('Y-m-d h:i'), $update_time);

    }

    public function test_update_device_user_check_table_if_created_history_date_updated_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $user = User::first();

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $serial_number = Str::random(10);

        $data = [
            "user_id" => $user->id,
            "sim_card_id" => $device->sim_card_id,
            "serial_number" => $device->serial_number,
            "imei" => $device->imei,
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 0
        ];

        $response = $this->putJson($url, $data);
        $response
        ->assertStatus(200);

        $device = Device::find($device->id);

        //dd($device->latestUser[0]);
        $this->assertEquals($user->id, $device->latestUser[0]->id);
        
    }

    public function test_update_device_with_sim_card_taken_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $deviceSecond = Device::orderBy('id', 'asc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $data = [
            "user_id" => $device->user_id,
            "sim_card_id" => $deviceSecond->sim_card_id,
            "serial_number" => Str::random(10),
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->putJson($url, $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
            ]);
    }

    public function test_update_device_with_serial_taken_taken_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $deviceSecond = Device::orderBy('id', 'asc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $data = [
            "user_id" => $device->user_id,
            "sim_card_id" => $device->sim_card_id,
            "serial_number" => $deviceSecond->serial_number,
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->putJson($url, $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => true,
            ]);
    }

    public function test_update_device_with_no_device_found_taken_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $deviceSecond = Device::orderBy('id', 'asc')->first();
        $url = '/api/v1/devices/' . intval($device->id) + 100;

        $data = [
            "user_id" => $device->user_id,
            "sim_card_id" => $device->sim_card_id,
            "serial_number" => $deviceSecond->serial_number,
            "imei" => Str::random(10),
            "model" => fake()->word(),
            "manufacturer" => fake()->randomElement(array: ['Apple', 'Samsung', 'Sony']),
            "operating_system" => fake()->randomElement(array: ['ios', 'andriod']),
            "type" => fake()->randomElement(array: ['mobile', 'tablet']),
            "is_active" => 1
        ];

        $response = $this->putJson($url, $data);
        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => true,
            ]);
    }

    public function test_delete_device_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . $device->id;

        $response = $this->deleteJson($url);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
    }

    public function test_delete_device_with_no_device_found_taken_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/' . intval($device->id) + 100;

        $response = $this->deleteJson($url);
        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => true,
            ]);
    }

    public function test_generate_export_link_and_download_link_with_credentials(): void 
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        $device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/generate-export-link';

        $response = $this->getJson($url);
        $response->assertStatus(200);

        $data = $response->json();

        $params = [
            'export_id' => $data['data']['export_id'],
            'file_name' => $data['data']['file_name']
        ];

        $response = $this->getJson($url, $params);
        $response->assertStatus(200);
           
    }

    public function test_job_queues(){

        Queue::fake();

        $chunkSize = 10;
        $devicesCount = Device::count();
        $numberOfChunks = ceil($devicesCount / $chunkSize);

        $file = now()->toDateString() . '-' . str_replace(':', '-', now()->toTimeString()).'-devices.csv';

        $batches = [
            new ProcessDeviceExport($chunkSize, $file)
        ];

        $this->assertFileExists(storage_path('app/public/'.$file));
        // Dispatch the job
        dispatch(new ProcessDeviceExport($chunkSize, $file));

        // Process the job
        Queue::assertPushed(ProcessDeviceExport::class);
    }

    public function test_task_scheduling(){

        $scheduledJobEvents = $this->getScheduledEventsForCommand(PruningDeactivatedDevices::class);
        $this->assertCount(1, $scheduledJobEvents);
    }

    private function getScheduledEventsForCommand(string $commandName, Carbon $atTime=null): Collection {
 

        $schedule = $this->app->get(Schedule::class);

        return collect($schedule->events())->filter(function(Event $event) use($commandName, $atTime){
            if(!str_contains((string)$event->command, $commandName) && strcmp((string)$event->description, $commandName) !== 0) {
                return false;
            }

            # optionally filter out events that are not due at the given time.
            if($atTime !== null) {
                Carbon::setTestNow($atTime);
                return $event->isDue($this->app) && $event->filtersPass($this->app);

            } else {
                return true;
            }
        });
    }

    public function test_get_list_of_users_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        //$device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/users';

        $response = $this->getJson($url);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
    }

    public function test_get_list_of_sim_cards_with_credentials(): void
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user,
        );

        //$device = Device::orderBy('id', 'desc')->first();
        $url = '/api/v1/devices/sim-cards';

        $response = $this->getJson($url);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
    }

}
