<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retrieval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('parking_tickets')->cascadeOnDelete();
            $table->foreignId('requested_by_guest_token_id')->nullable()->constrained('qr_tokens')->nullOnDelete();
            $table->unsignedSmallInteger('requested_minutes')->default(10);
            $table->timestamp('requested_at');
            $table->timestamp('target_ready_at');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('REQUESTED');
            $table->text('cancellation_reason')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['hotel_id', 'status', 'requested_at']);
            $table->index(['ticket_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retrieval_requests');
    }
};

