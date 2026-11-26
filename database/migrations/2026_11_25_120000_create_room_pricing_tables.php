<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('days')->default('all');
            $table->unsignedSmallInteger('priority')->default(0);
            $table->decimal('nightly_lkr', 12, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['villa_id', 'is_active', 'starts_on', 'ends_on']);
        });
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('lkr_per_usd', 14, 6);
            $table->string('source');
            $table->timestamp('fetched_at');
            $table->boolean('is_override')->default(false);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('villa_rates');
    }
};
