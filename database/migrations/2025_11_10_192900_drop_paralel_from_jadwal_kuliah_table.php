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
        if (Schema::hasColumn('jadwal_kuliah', 'paralel')) {
            Schema::table('jadwal_kuliah', function (Blueprint $table) {
                $table->dropColumn('paralel');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('jadwal_kuliah', 'paralel')) {
            Schema::table('jadwal_kuliah', function (Blueprint $table) {
                $table->string('paralel', 5)->nullable()->after('tanggal_selesai')->comment('Kode paralel (opsional)');
            });
        }
    }
};
