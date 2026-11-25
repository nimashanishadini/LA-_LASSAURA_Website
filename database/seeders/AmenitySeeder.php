<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $amenities = [
            ['name' => 'Wi-Fi', 'slug' => 'wifi', 'icon' => 'wifi'],
            ['name' => 'Air Conditioning', 'slug' => 'air-conditioning', 'icon' => 'snowflake'],
            ['name' => 'Swimming Pool', 'slug' => 'swimming-pool', 'icon' => 'waves'],
            ['name' => 'Private Bathroom', 'slug' => 'private-bathroom', 'icon' => 'bath'],
            ['name' => 'Parking', 'slug' => 'parking', 'icon' => 'car'],
            ['name' => 'Garden View', 'slug' => 'garden-view', 'icon' => 'trees'],
            ['name' => 'Balcony', 'slug' => 'balcony', 'icon' => 'sun'],
            ['name' => 'King Size Bed', 'slug' => 'king-size-bed', 'icon' => 'bed-double'],
        ];

        foreach ($amenities as $index => $amenity) {
            Amenity::firstOrCreate(
                ['slug' => $amenity['slug']],
                [
                    'name' => $amenity['name'],
                    'icon' => $amenity['icon'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
