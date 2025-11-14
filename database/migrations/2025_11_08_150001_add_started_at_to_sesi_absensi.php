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
        if (!Schema::hasColumn('sesi_absensi', 'started_at')) {
            Schema::table('sesi_absensi', function (Blueprint $table) {
                $table->timestamp('started_at')->nullable()->after('status');
                $table->integer('started_by')->nullable()->after('started_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sesi_absensi', 'started_at')) {
            Schema::table('sesi_absensi', function (Blueprint $table) {
                $table->dropColumn(['started_at', 'started_by']);
            });
        }
    }
};
