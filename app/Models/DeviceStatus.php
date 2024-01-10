<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceStatus extends Model
{
    use HasFactory;

    protected $table = 'device_status';
    
    public function device() : BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
