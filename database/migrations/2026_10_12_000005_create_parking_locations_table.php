<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('zone');
            $table->string('floor')->nullable();
            $table->string('slot')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['hotel_id', 'active']);
            $table->index(['hotel_id', 'zone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_locations');
    }
};

