<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SimCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'network_provider_id',
        'serial',
        'name',
    ];

    public function device () : HasOne
    {
        return $this->hasOne(Device::class);
    }

    public function phoneNumber () : HasOne
    {
        return $this->hasOne(PhoneNumber::class,  'sim_card_id');
    }

    public function networkProvider () : BelongsTo
    {
        return $this->belongsTo(NetworkProvider::class, 'network_provider_id');
    }
    
}
