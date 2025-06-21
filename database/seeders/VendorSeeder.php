<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Royal Catering Services',
                'email' => 'info@royalcatering.com',
                'phone' => '+1-555-0101',
                'category' => 'catering',
                'description' => 'Premium catering services for weddings and special events. We offer a wide variety of cuisines and custom menu planning.',
                'price_range' => '$50-100 per person',
                'location' => 'New York, NY',
                'rating' => 4.8,
                'services' => ['Indian Cuisine', 'Continental', 'Chinese', 'Live Counters', 'Dessert Station']
            ],
            [
                'name' => 'Elegant Decorations',
                'email' => 'contact@elegantdeco.com',
                'phone' => '+1-555-0102',
                'category' => 'decoration',
                'description' => 'Transform your venue into a magical space with our creative decoration services.',
                'price_range' => '$2000-8000',
                'location' => 'Los Angeles, CA',
                'rating' => 4.9,
                'services' => ['Floral Arrangements', 'Stage Decoration', 'Lighting', 'Mandap Setup', 'Entrance Decor']
            ],
            [
                'name' => 'Capture Moments Photography',
                'email' => 'hello@capturemoments.com',
                'phone' => '+1-555-0103',
                'category' => 'photography',
                'description' => 'Professional wedding photography and videography services to capture your special moments.',
                'price_range' => '$1500-5000',
                'location' => 'Chicago, IL',
                'rating' => 4.7,
                'services' => ['Pre-wedding Shoot', 'Wedding Photography', 'Videography', 'Drone Shots', 'Album Design']
            ],
            [
                'name' => 'Beats & Rhythms DJ',
                'email' => 'bookings@beatsrhythms.com',
                'phone' => '+1-555-0104',
                'category' => 'music',
                'description' => 'Professional DJ services with state-of-the-art sound systems and lighting.',
                'price_range' => '$800-2500',
                'location' => 'Miami, FL',
                'rating' => 4.6,
                'services' => ['DJ Services', 'Sound System', 'Lighting', 'Live Band', 'Karaoke Setup']
            ],
            [
                'name' => 'Grand Palace Banquet',
                'email' => 'reservations@grandpalace.com',
                'phone' => '+1-555-0105',
                'category' => 'venue',
                'description' => 'Luxurious banquet hall perfect for wedding ceremonies and receptions.',
                'price_range' => '$5000-15000',
                'location' => 'Las Vegas, NV',
                'rating' => 4.9,
                'services' => ['Air Conditioned Hall', 'Parking', 'Catering Kitchen', 'Bridal Suite', 'Garden Area']
            ],
            [
                'name' => 'Glamour Makeup Studio',
                'email' => 'appointments@glamourmakeup.com',
                'phone' => '+1-555-0106',
                'category' => 'makeup',
                'description' => 'Professional bridal makeup and styling services for your perfect look.',
                'price_range' => '$300-800',
                'location' => 'San Francisco, CA',
                'rating' => 4.8,
                'services' => ['Bridal Makeup', 'Hair Styling', 'Mehendi Design', 'Saree Draping', 'Trial Session']
            ],
            [
                'name' => 'Luxury Transport Services',
                'email' => 'bookings@luxurytransport.com',
                'phone' => '+1-555-0107',
                'category' => 'transport',
                'description' => 'Premium transportation services for weddings including luxury cars and decorated vehicles.',
                'price_range' => '$200-1000',
                'location' => 'Boston, MA',
                'rating' => 4.5,
                'services' => ['Luxury Cars', 'Decorated Vehicles', 'Horse Carriage', 'Vintage Cars', 'Guest Transportation']
            ]
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }
}
