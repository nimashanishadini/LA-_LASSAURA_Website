<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryEvent extends Model
{
    protected $fillable = ['actor_id', 'booking_id', 'maintenance_block_id', 'action', 'details'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
