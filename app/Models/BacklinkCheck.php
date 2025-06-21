<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BacklinkCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'backlink_id',
        'status_code',
        'is_active',
        'is_dofollow',
        'anchor_text',
        'response_time',
        'checked_at',
        'error_message',
        'exact_match',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_dofollow' => 'boolean',
            'checked_at' => 'datetime',
            'exact_match' => 'boolean',
        ];
    }

    public function backlink()
    {
        return $this->belongsTo(Backlink::class);
    }
}
