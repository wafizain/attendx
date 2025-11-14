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
        if (Schema::hasTable('kelas') && !Schema::hasColumn('kelas', 'nama')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->string('nama', 100)->after('kode')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('kelas', 'nama')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropColumn('nama');
            });
        }
    }
};
