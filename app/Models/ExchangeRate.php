<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['lkr_per_usd', 'source', 'fetched_at', 'is_override', 'recorded_by', 'reason'];

    protected function casts(): array
    {
        return ['lkr_per_usd' => 'decimal:6', 'fetched_at' => 'immutable_datetime', 'is_override' => 'boolean'];
    }

    public function isStale(): bool
    {
        return $this->fetched_at->lt(now()->subHours(config('pricing.stale_hours')));
    }
}
