<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneNumber extends Model
{
    use HasFactory;
    protected $fillable = [
        'sim_card_id',
        'number',
    ];

    public function simCard() : BelongsTo
    {
        return $this->belongsTo(SimCard::class, 'sim_card_id');
    }
}
