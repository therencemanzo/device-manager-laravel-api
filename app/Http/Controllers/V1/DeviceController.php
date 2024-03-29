<?php

namespace App\Http\Controllers\V1;


use App\Http\Resources\SimCardResource;
use Illuminate\Http\Request;
use App\Models\DeviceUserHistory;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Models\SimCard;
use App\Http\Resources\DeviceResource;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Jobs\ProcessDeviceExport;
use App\Jobs\AppendMoreDevices;
use App\Jobs\SendEmailToUserWhenDeviceDeactivated;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * @group Device management
 *
 * APIs for managing devices
 */

class DeviceController extends Controller
{

    /**
        * Device List
        *
        * This endpoint allows you to get all the devices.
        * @queryParam os string the operating sytem you want to filter. Example: ios
        * @queryParam q string the query/filter you want to search for imei, model, serial, manufacturer of the device. Example: samsung
        * @subgroup Main
        * @subgroupDescription crud operation for devices
        * @authenticated
    */
    public function index(Request $request)
    {
        
        $devices = Device::search($request->q)
        ->when(isset($request->os), function($query) use ($request){

            if($request->os == 'ios' || $request->os == 'andriod'){
                return $query->where('operating_system', $request->os);
            }

        })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return DeviceResource::collection($devices);
    }

    /**
        * Add Device
        *
        * This endpoint allows you to add a devices.
        * @bodyParam user_id int required the user you want to assign this device, it must exist in the user list. Example: 1
        * @bodyParam sim_card_id int required the sim card you want to assign to this device, it must exist in the sim list and doenst belong to any device yet. Example: 1
        * @bodyParam serial_number string required it must be unique. Example: SMG19999586660001
        * @bodyParam imei string required it must be unique. Example: SNG1800
        * @bodyParam model string model of the device. Example Samsung: Galaxy
        * @bodyParam manufacturer string manufacturer of the device. Example: Samsung 
        * @bodyParam operating_system string required the operating system of the device it should be ios or andriod. Example: andriod 
        * @bodyParam type string required the type of the device it should be mobile or tablet. Example: mobile 
        * @subgroup Main
        * @subgroupDescription crud operation for devices
        * @authenticated
    */
    public function store(StoreDeviceRequest $request)
    {
        $device = Device::create($request->safe()->except(['user_id']));
        $device->users()->syncWithoutDetaching([$request['user_id']]);

        $updateData = Device::find($device->id);

        return DeviceResource::make($updateData)
        ->response()
        ->setStatusCode(201);;
    }

    /**
        * View Device
        * 
        * This endpoint allows you to view a devices more details.
        * @urlParam device_id int required The id of the device. Example: 1
        * @subgroup Main
        * @subgroupDescription crud operation for devices
        * @authenticated
    */
    public function show(Device $device)
    {
        return DeviceResource::make($device);
    }

    /**
        * Update Device
        *
        * This endpoint allows you to update a devices.
        * @bodyParam user_id int required the user you want to assign this device, it must exist in the user list. Example: 1
        * @bodyParam sim_card_id int required the sim card you want to assign to this device, it must exist in the sim list and doenst belong to any device yet. Example: 1
        * @bodyParam serial_number string required it must be unique. Example: SMG19999586660001
        * @bodyParam imei string required it must be unique. Example: SNG1800
        * @bodyParam model string model of the device. Example Samsung: Galaxy
        * @bodyParam manufacturer string manufacturer of the device. Example: Samsung 
        * @bodyParam operating_system string required the operating system of the device it should be ios or andriod. Example: andriod 
        * @bodyParam type string required the type of the device it should be mobile or tablet. Example: mobile 
        * @bodyParam is_active int if you want to update the status of the device 1 for active 0 for inactive. Example: 0 
        * @subgroup Main
        * @subgroupDescription crud operation for devices
        * @authenticated
    */
    public function update(UpdateDeviceRequest $request, Device $device)
    {   
        $validated = $request->validated();

        $device->update($request->safe()->except(['user_id']));
        

        //update status active date for pruning
        $this->updateStatus($request, $device, 0);
        $device->users()->attach($validated['user_id']);

        return DeviceResource::make($device);

    }

    /**
        * Update Device Status
        *
        * @urlParam device_id int required The id of the device. Example: 1
        * This endpoint allows you to update the status of the devices.
        * @bodyParam is_active int required if you want to update the status of the device 1 for active 0 for inactive. Example: 0 
        * @subgroup Main
        * @subgroupDescription crud operation for devices
        * @authenticated
    */

    public function updateStatus(Request $request, Device $device, $withResponse = 1){

        $validated = $request->validated();
        $device->is_active = $validated['is_active'];

        if($validated['is_active'] == 0){
            
            $device->status_update_at = date('Y-m-d H:i:s');
            $device->save();
            dispatch(new SendEmailToUserWhenDeviceDeactivated($device->latestUser[0]));
        }

        if($withResponse == 1){
            return response()->json([
                'data' => [
                    'message' => 'Status Updated'
                ]
            ], 200);
        }

    }

    /**
        * Delete Device
        *
        * This endpoint allows you to delete a devices.
        * @subgroup Main
        * @subgroupDescription crud operation for devices
        * @authenticated
    */
    public function destroy(Device $device)
    {
        $device->delete();

        return response()->json([
            'data' => [
                'message' => 'Deleted'
            ]
        ], 200);
    }

    /**
        * Generate Export CSV
        *
        * This endpoint allows you to generate csv for the devices so you can export it.
        * @subgroup Utilities
        * @subgroupDescription generate and download csv
        * @authenticated
    */

    public function exportDevices(){

        $chunkSize = 10;
        $devicesCount = Device::count();
        $numberOfChunks = ceil($devicesCount / $chunkSize);

        $file = now()->toDateString() . '-' . str_replace(':', '-', now()->toTimeString()).'-devices.csv';

        $batches = [
            new ProcessDeviceExport($chunkSize, $file)
        ];

        if ($devicesCount > $chunkSize) {
            $numberOfChunks = $numberOfChunks - 1;
            for ($numberOfChunks; $numberOfChunks > 0; $numberOfChunks--) {
                $batches[] = new AppendMoreDevices($numberOfChunks, $chunkSize, $file);
            }
        }

        $batch = Bus::batch($batches)
        ->name('exporting-devices')
        ->dispatch();

        return response()->json([
            'data' => [
                'export_id' => $batch->id,
                'file_name' => $file
            ]
        ], 200);

    }

    /**
        * Get Export CSV download link
        *
        * This endpoint allows you to get the download link of the csv generated.
        * @queryParam export_id required the id of the generated export link
        * @queryParam filename required the filename of the generated export link
        * @subgroup Utilities
        * @subgroupDescription generate and download csv
        * @authenticated
    */
    public function downloadCsv(Request $request){

        $validated = $request->validate([
            'export_id' => ['required', 'string'],
            'file_name' => ['required', 'string'],
        ]);
        
        $filePath = $validated['file_name'];

        return response()->json([
            'data' => [
                'export_details' => Bus::findBatch($validated['export_id']),
                'download_link' => url('download/'.$filePath)
            ]
        ], 200);
    }


}
