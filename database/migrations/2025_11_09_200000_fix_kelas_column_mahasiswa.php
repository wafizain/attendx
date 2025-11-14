<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix kolom lama di tabel mahasiswa
        if (Schema::hasTable('mahasiswa')) {
            // Hapus kolom 'kelas' lama (sudah ada 'id_kelas')
            if (Schema::hasColumn('mahasiswa', 'kelas')) {
                DB::statement('ALTER TABLE `mahasiswa` DROP COLUMN `kelas`');
            }
            
            // Hapus kolom 'prodi' lama (sudah ada 'id_prodi')
            if (Schema::hasColumn('mahasiswa', 'prodi')) {
                DB::statement('ALTER TABLE `mahasiswa` DROP COLUMN `prodi`');
            }
            
            // Hapus kolom lama lainnya yang mungkin ada
            if (Schema::hasColumn('mahasiswa', 'jurusan')) {
                DB::statement('ALTER TABLE `mahasiswa` DROP COLUMN `jurusan`');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: rollback
    }
};
