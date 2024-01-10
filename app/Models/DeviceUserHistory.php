<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceUserHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id'
    ];
    
    protected $table = 'device_user_history';
}
