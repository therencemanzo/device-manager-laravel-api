<?php

namespace App\Exports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DevicesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    public function query()
    {
        return Device::query()->with('user')->with('simCard');
    }

    public function headings(): array
    {
        return [
            '#',
            'user',
            'sim',
            'phonenumber',
            'model',
            'manufacturer'
        ];
    }

    public function map($device): array
    {
        return [
            $device->id,
            $device->user->name,
            $device->simCard->name,
            $device->simCard->phoneNumber->number,
            $device->model,
            $device->manufacturer
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'simCard',
            'model',
            'user',
            'manufacturer'
        ];
    }
}
