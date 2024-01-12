<?php

namespace App\Jobs;

use App\Models\Device;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AppendMoreDevices implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $chunkIndex, public $chunkSize, public $file)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = Device::query()
        ->skip($this->chunkIndex * $this->chunkSize)
        ->take($this->chunkSize)
        ->get()
        ->map(function ($device) {
            return [
                $device->id,
                $device->latestUser[0]->name,
                $device->simCard->name,
                $device->simCard->phoneNumber->number,
                $device->model,
                $device->manufacturer,
            ];
        });

        $filePath = storage_path("app/{$this->file}");

        // Open the file in write mode and create it if it doesn't exist
        $file = fopen($filePath, 'a+');

        // Write the data to the CSV file
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    }
}
