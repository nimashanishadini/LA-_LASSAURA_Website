<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingQuote extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'user_id', 'villa_id', 'check_in', 'check_out', 'adults', 'children', 'snapshot', 'terms', 'expires_at'];

    protected function casts(): array
    {
        return ['check_in' => 'immutable_date', 'check_out' => 'immutable_date', 'adults' => 'integer', 'children' => 'integer', 'snapshot' => 'array', 'terms' => 'array', 'expires_at' => 'immutable_datetime'];
    }

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
