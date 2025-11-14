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
        Schema::table('program_studi', function (Blueprint $table) {
            // Add fakultas as VARCHAR if not exists
            if (!Schema::hasColumn('program_studi', 'fakultas')) {
                $table->string('fakultas', 100)->nullable()->after('jenjang')->comment('Nama fakultas');
            }
            
            // Add deskripsi if not exists
            if (!Schema::hasColumn('program_studi', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('status')->comment('Deskripsi singkat prodi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            if (Schema::hasColumn('program_studi', 'fakultas')) {
                $table->dropColumn('fakultas');
            }
            if (Schema::hasColumn('program_studi', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
        });
    }
};
