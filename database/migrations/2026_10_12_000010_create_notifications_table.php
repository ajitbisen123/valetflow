<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valet_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('retrieval_request_id')->nullable()->constrained('retrieval_requests')->nullOnDelete();
            $table->string('channel')->default('in_app');
            $table->string('message_type');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_summary')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valet_notifications');
    }
};

