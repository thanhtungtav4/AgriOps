<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->decimal('estimated_margin', 14, 2)->nullable()->after('estimated_revenue');
            $table->json('pricing_snapshot')->nullable()->after('margin_percent');
        });
    }

    public function down(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->dropColumn(['estimated_margin', 'pricing_snapshot']);
        });
    }
};
