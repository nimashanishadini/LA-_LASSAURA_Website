<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VillaMedia extends Model
{
    use HasFactory;

    protected $table = 'villa_media';

    protected $fillable = [
        'villa_id',
        'disk',
        'path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}