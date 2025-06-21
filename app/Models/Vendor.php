<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'category',
        'description',
        'price_range',
        'location',
        'rating',
        'image',
        'services',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'bookings')
                    ->withPivot('status', 'amount', 'booking_date')
                    ->withTimestamps();
    }
}
