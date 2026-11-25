<?php

namespace Database\Seeders;

use App\Models\Villa;
use Illuminate\Database\Seeder;

class VillaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Sandy' => 'family', 'Shelly' => 'family', 'Coral' => 'double', 'Pearl' => 'double'] as $name => $category) {
            Villa::firstOrCreate(['slug' => strtolower($name)], [
                'name' => $name,
                'category' => $category,
                'max_adults' => null,
                'max_children' => null,
                'max_guests' => null,
                'is_active' => false,
            ]);
        }
    }
}
