<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    protected $guarded = [];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return ['attempts' => 'integer', 'expires_at' => 'immutable_datetime', 'sent_at' => 'immutable_datetime', 'consumed_at' => 'immutable_datetime'];
    }
}
