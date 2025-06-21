<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backlink extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'source_url',
        'target_url',
        'anchor_text',
        'domain_authority',
        'page_authority',
        'is_active',
        'is_dofollow',
        'status_code',
        'last_checked_at',
        'first_found_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_dofollow' => 'boolean',
            'last_checked_at' => 'datetime',
            'first_found_at' => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function checks()
    {
        return $this->hasMany(BacklinkCheck::class);
    }

    public function latestCheck()
    {
        return $this->hasOne(BacklinkCheck::class)->latest();
    }

    public function getSourceDomainAttribute()
    {
        return parse_url($this->source_url, PHP_URL_HOST);
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status_code) {
            200 => 'bg-green-100 text-green-800',
            404 => 'bg-red-100 text-red-800',
            301, 302 => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
