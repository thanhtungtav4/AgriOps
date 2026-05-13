<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('climate_zone')->nullable();
            $table->string('responsible_person')->nullable();
            $table->decimal('total_area_m2', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->jsonb('certification')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('climate_zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};