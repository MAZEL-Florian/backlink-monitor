<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'domain',
        'description',
        'is_active',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function backlinks()
    {
        return $this->hasMany(Backlink::class);
    }

    public function activeBacklinks()
    {
        return $this->hasMany(Backlink::class)->where('is_active', true);
    }

    public function inactiveBacklinks()
    {
        return $this->hasMany(Backlink::class)->where('is_active', false);
    }

    public function getBacklinksCountAttribute()
    {
        return $this->backlinks()->count();
    }

    public function getActiveBacklinksCountAttribute()
    {
        return $this->activeBacklinks()->count();
    }
}
