<?php

namespace Database\Seeders;

use App\Models\BookingPolicy;
use Illuminate\Database\Seeder;

class BookingPolicySeeder extends Seeder
{
    public function run(): void
    {
        BookingPolicy::firstOrCreate(['key' => 'default']);
    }
}
