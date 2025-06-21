<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BacklinkCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'backlink_id',
        'source_domain',
        'target_url',
        'status_code',
        'is_active',
        'is_dofollow',
        'anchor_text',
        'response_time',
        'checked_at',
        'error_message',
        'exact_match',
        'domain_authority',
        'page_authority',
        'notes',
        'first_found_at',
        'metadata',
        'check_type',
        'raw_response',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_dofollow' => 'boolean',
        'exact_match' => 'boolean',
        'metadata' => 'array',
        'checked_at' => 'datetime',
        'first_found_at' => 'datetime',
    ];

    public function backlink()
    {
        return $this->belongsTo(Backlink::class);
    }

    public static function createFromBacklink(Backlink $backlink, array $checkResult, string $checkType = 'automatic'): self
    {
        return self::create([
            'backlink_id' => $backlink->id,
            'source_domain' => $backlink->source_domain,
            'target_url' => $checkResult['target_url'] ?? $backlink->target_url,
            'status_code' => $checkResult['status_code'],
            'is_active' => $checkResult['is_active'],
            'is_dofollow' => $checkResult['is_dofollow'],
            'anchor_text' => $checkResult['anchor_text'] ?? $backlink->anchor_text,
            'response_time' => $checkResult['response_time'],
            'checked_at' => now(),
            'error_message' => $checkResult['error_message'] ?? null,
            'exact_match' => $checkResult['exact_match'] ?? false,
            'domain_authority' => $backlink->domain_authority,
            'page_authority' => $backlink->page_authority,
            'notes' => $backlink->notes,
            'first_found_at' => $backlink->first_found_at,
            'metadata' => [
                'project_id' => $backlink->project_id,
                'project_name' => $backlink->project->name,
                'project_domain' => $backlink->project->domain,
                'source_url' => $backlink->source_url,
                'user_agent' => $checkResult['user_agent'] ?? null,
                'redirects' => $checkResult['redirects'] ?? [],
                'content_length' => $checkResult['content_length'] ?? null,
                'content_type' => $checkResult['content_type'] ?? null,
            ],
            'check_type' => $checkType,
            'raw_response' => $checkResult['raw_response'] ?? null,
        ]);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('check_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('checked_at', '>=', now()->subDays($days));
    }

    public function getStatusSummaryAttribute(): string
    {
        if ($this->error_message) {
            return "Erreur: {$this->error_message}";
        }
        
        $status = $this->is_active ? 'ACTIF' : 'INACTIF';
        $follow = $this->is_dofollow ? 'DoFollow' : 'NoFollow';
        $code = $this->status_code ? "HTTP {$this->status_code}" : 'N/A';
        
        return "{$status} | {$follow} | {$code}";
    }

    public function getCheckedAtAttribute($value)
    {
        if (is_string($value)) {
            return Carbon::parse($value);
        }
        return $value;
    }
}
