<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
 
class Device extends Model
{
    use HasFactory,Searchable;

    protected $fillable = [
        'user_id',
        'sim_card_id',
        'serial_number',
        'imei',
        'model',
        'manufacturer',
        'operating_sytem',
        'type',
        'is_active',
        'status_update_at'
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function simCard() : BelongsTo
    {
        return $this->belongsTo(SimCard::class, 'sim_card_id');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    #[SearchUsingPrefix(['serial_number'])]
    public function toSearchableArray(): array
    {
        return [
            'serial_number' => $this->serial_number,
            'model' => $this->model,
            'manufacturer' => $this->manufacturer,
            'imei' => $this->imei
        ];
    }
}
