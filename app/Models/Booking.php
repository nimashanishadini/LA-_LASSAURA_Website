<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $hidden = ['submission_hash'];

    protected $fillable = ['reference', 'villa_id', 'check_in', 'check_out', 'adults', 'children', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['check_in' => 'immutable_date', 'check_out' => 'immutable_date', 'adults' => 'integer', 'children' => 'integer', 'quote_snapshot' => 'array', 'terms_snapshot' => 'array', 'proposed_snapshot' => 'array', 'proposed_terms' => 'array', 'confirmed_at' => 'immutable_datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
