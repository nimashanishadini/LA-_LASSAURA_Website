<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('villa_id')
                ->constrained('villas')
                ->cascadeOnDelete();

            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('alt_text')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['villa_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_media');
    }
};