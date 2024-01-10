<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_card_id',
        'number',
    ];

    public function SimCard () : HasMany
    {
        return $this->hasMany(SimCard::class, 'network_provider_id');
    }
}
