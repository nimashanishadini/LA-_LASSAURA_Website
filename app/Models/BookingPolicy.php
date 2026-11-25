<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPolicy extends Model
{
    protected $fillable = [
        'key', 'check_in_time', 'check_out_time',
        'cancellation_policy', 'allows_multiple_rooms',
    ];

    protected function casts(): array
    {
        return ['allows_multiple_rooms' => 'boolean'];
    }
}
