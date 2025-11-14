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
        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'paralel')) {
                $table->dropColumn('paralel');
            }
            if (Schema::hasColumn('kelas', 'ruangan_default')) {
                $table->dropColumn('ruangan_default');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (!Schema::hasColumn('kelas', 'paralel')) {
                $table->string('paralel', 8)->nullable()->after('angkatan');
            }
            if (!Schema::hasColumn('kelas', 'ruangan_default')) {
                $table->string('ruangan_default', 50)->nullable()->after('kapasitas');
            }
        });
    }
};
