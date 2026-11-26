<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaNight extends Model
{
    protected $table = 'villa_nights';

    protected $fillable = ['villa_id', 'stay_date', 'booking_id', 'maintenance_block_id'];

    protected function casts(): array
    {
        return ['stay_date' => 'immutable_date'];
    }

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
