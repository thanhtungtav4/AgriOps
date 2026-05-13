<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farming_logs', function (Blueprint $table) {
            $table->string('client_uuid', 120)->nullable()->after('reported_by_user_id');
            $table->unique(['work_task_id', 'client_uuid']);
        });
    }

    public function down(): void
    {
        Schema::table('farming_logs', function (Blueprint $table) {
            $table->dropUnique(['work_task_id', 'client_uuid']);
            $table->dropColumn('client_uuid');
        });
    }
};
