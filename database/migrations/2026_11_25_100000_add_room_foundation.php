<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->string('category')->nullable()->index();
            $table->unsignedSmallInteger('max_adults')->nullable()->change();
            $table->unsignedSmallInteger('max_children')->nullable()->default(null)->change();
            $table->unsignedSmallInteger('max_guests')->nullable()->change();
        });
        Schema::create('booking_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->boolean('allows_multiple_rooms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_policies');
        Schema::table('villas', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
        // Preserve nullable capacities rather than invent values during rollback.
    }
};
