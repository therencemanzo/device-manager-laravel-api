<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\DeviceController;
use Illuminate\Support\Facades\Bus;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {

    
    Route::post('login', [AuthController::class, 'login'])->name('login');
    
    Route::middleware(['auth:sanctum','can:manage_devices'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('devices/generate-export-link', [DeviceController::class, 'exportDevices'])->name('device.generate.export.link');
        Route::get('devices/get-download-link',[DeviceController::class, 'downloadCsv'])->name('device.export.download.link');

        Route::get('devices/sim-cards', [DeviceController::class, 'getSimCards'])->name('device.get.sim.cards');
        Route::get('devices/users', [DeviceController::class, 'getUsers'])->name('device.get.users');

        Route::apiResource('devices', DeviceController::class);
        Route::patch('devices/status/{device}', [DeviceController::class, 'updateStatus'])->name('device.status.update');
    
    });

   
});