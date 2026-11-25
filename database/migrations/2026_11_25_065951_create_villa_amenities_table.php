
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_amenities', function (Blueprint $table) {

            $table->foreignId('villa_id')
                ->constrained('villas')
                ->cascadeOnDelete();

            $table->foreignId('amenity_id')
                ->constrained('amenities')
                ->cascadeOnDelete();

            $table->primary(['villa_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_amenities');
    }
};
