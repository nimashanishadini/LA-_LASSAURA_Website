<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('villa_id')->constrained()->restrictOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('adults');
            $table->unsignedSmallInteger('children')->default(0);
            $table->string('status')->default('pending')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('maintenance_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('reason', 500);
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('villa_nights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained()->restrictOnDelete();
            $table->date('stay_date');
            $table->foreignId('booking_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('maintenance_block_id')->nullable()->constrained()->restrictOnDelete();
            $table->unique(['villa_id', 'stay_date']);
            $table->timestamps();
        });
        // Database-level exactly-one-owner constraint, including SQLite test schema.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE villa_nights ADD CONSTRAINT villa_nights_one_owner CHECK ((booking_id IS NOT NULL AND maintenance_block_id IS NULL) OR (booking_id IS NULL AND maintenance_block_id IS NOT NULL))');
        } else {
            DB::unprepared("CREATE TRIGGER villa_nights_owner_insert BEFORE INSERT ON villa_nights WHEN ((NEW.booking_id IS NULL) = (NEW.maintenance_block_id IS NULL)) BEGIN SELECT RAISE(ABORT, 'Exactly one inventory owner required'); END");
            DB::unprepared("CREATE TRIGGER villa_nights_owner_update BEFORE UPDATE ON villa_nights WHEN ((NEW.booking_id IS NULL) = (NEW.maintenance_block_id IS NULL)) BEGIN SELECT RAISE(ABORT, 'Exactly one inventory owner required'); END");
        }
        Schema::create('inventory_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('maintenance_block_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action');
            $table->json('details');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_events');
        Schema::dropIfExists('villa_nights');
        Schema::dropIfExists('maintenance_blocks');
        Schema::dropIfExists('bookings');
    }
};
