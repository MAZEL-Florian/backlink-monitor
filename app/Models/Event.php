<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_date',
        'venue',
        'budget',
        'guest_count',
        'status',
        'image',
        'requirements',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'requirements' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'bookings')
                    ->withPivot('status', 'amount', 'booking_date')
                    ->withTimestamps();
    }
}
