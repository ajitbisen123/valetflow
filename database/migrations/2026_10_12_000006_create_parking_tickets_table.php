<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('ticket_number');
            $table->foreignId('guest_session_id')->nullable()->constrained('guest_sessions')->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parking_location_id')->nullable()->constrained('parking_locations')->nullOnDelete();
            $table->string('status')->default('CHECKED_IN');
            $table->timestamp('checked_in_at');
            $table->timestamp('parked_at')->nullable();
            $table->timestamp('retrieval_requested_at')->nullable();
            $table->timestamp('ready_at_gate_at')->nullable();
            $table->timestamp('handed_over_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hotel_id', 'ticket_number']);
            $table->index(['hotel_id', 'status', 'created_at']);
            $table->index(['hotel_id', 'assigned_driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_tickets');
    }
};

