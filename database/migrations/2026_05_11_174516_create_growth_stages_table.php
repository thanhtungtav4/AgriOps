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
        Schema::create('growth_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->unsignedSmallInteger('duration_days')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['crop_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_stages');
    }
};
