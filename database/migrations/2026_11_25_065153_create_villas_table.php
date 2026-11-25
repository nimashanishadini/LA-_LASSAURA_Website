<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villas', function (Blueprint $table) {
            $table->id();

            // Basic villa information
            $table->string('name');
            $table->string('slug')->unique();

            // Descriptions
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // Guest capacity
            $table->unsignedSmallInteger('max_adults');
            $table->unsignedSmallInteger('max_children')->default(0);
            $table->unsignedSmallInteger('max_guests');

            // Website visibility
            $table->boolean('is_active')->default(false)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villas');
    }
};