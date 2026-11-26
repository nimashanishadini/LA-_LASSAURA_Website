<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('villa_id')->constrained()->restrictOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('adults');
            $table->unsignedSmallInteger('children');
            $table->json('snapshot');
            $table->json('terms');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->uuid('quote_id')->nullable()->unique();
            $table->foreign('quote_id')->references('id')->on('booking_quotes')->restrictOnDelete();
            $table->string('guest_name', 120)->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 30)->nullable();
            $table->text('special_requests')->nullable();
            $table->string('submission_hash', 64)->nullable();
            $table->json('quote_snapshot')->nullable();
            $table->json('terms_snapshot')->nullable();
            $table->json('proposed_snapshot')->nullable();
            $table->json('proposed_terms')->nullable();
            $table->string('rejection_reason', 1000)->nullable();
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['quote_id']);
            $table->dropUnique(['quote_id']);
            $table->dropColumn(['user_id', 'quote_id', 'guest_name', 'guest_email', 'guest_phone', 'special_requests', 'submission_hash', 'quote_snapshot', 'terms_snapshot', 'proposed_snapshot', 'proposed_terms', 'rejection_reason', 'confirmed_at']);
        });
        Schema::dropIfExists('booking_quotes');
    }
};
