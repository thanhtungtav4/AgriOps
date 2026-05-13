<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add farm_id to supply_contracts and supply_demands for farm scope isolation.
     * This is a tiny security migration required for RBAC enforcement.
     */
    public function up(): void
    {
        Schema::table('supply_contracts', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('farm_id');
        });

        Schema::table('supply_demands', function (Blueprint $table) {
            $table->foreignId('farm_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('farm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_demands', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->dropColumn('farm_id');
        });

        Schema::table('supply_contracts', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
            $table->dropColumn('farm_id');
        });
    }
};
