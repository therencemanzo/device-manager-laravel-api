<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sim_card' => new SimCardResource($this->simCard),
            'user' => $this->user->name,
            'serial_number' => $this->serial_number,
            'imei' => $this->imei,
            'model' => $this->model,
            'manufacturer' => $this->manufacturer,
            'operating_system' => $this->operating_system,
            'type' => $this->type,
            'active' => $this->is_active,
            'created_at' => $this->created_at->format('d-m-Y'),
        ];
    }
}
