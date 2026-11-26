<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceBlock extends Model
{
    protected $fillable = ['villa_id', 'starts_on', 'ends_on', 'reason', 'created_by', 'cancelled_at'];

    protected function casts(): array
    {
        return ['starts_on' => 'immutable_date', 'ends_on' => 'immutable_date', 'cancelled_at' => 'immutable_datetime'];
    }

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
