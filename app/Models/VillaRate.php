<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaRate extends Model
{
    protected $fillable = ['villa_id', 'name', 'starts_on', 'ends_on', 'days', 'priority', 'nightly_lkr', 'discount_percent', 'is_active', 'updated_by'];

    protected function casts(): array
    {
        return ['starts_on' => 'immutable_date', 'ends_on' => 'immutable_date', 'priority' => 'integer', 'nightly_lkr' => 'decimal:2', 'discount_percent' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
