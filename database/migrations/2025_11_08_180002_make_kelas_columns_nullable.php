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
        // Make mata_kuliah_id and dosen_id nullable
        if (Schema::hasTable('kelas')) {
            // Check if columns exist and make them nullable
            if (Schema::hasColumn('kelas', 'mata_kuliah_id')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `mata_kuliah_id` BIGINT UNSIGNED NULL');
            }
            
            if (Schema::hasColumn('kelas', 'dosen_id')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `dosen_id` BIGINT UNSIGNED NULL');
            }

            if (Schema::hasColumn('kelas', 'nama_kelas')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `nama_kelas` VARCHAR(100) NULL');
            }

            if (Schema::hasColumn('kelas', 'tahun_ajaran')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `tahun_ajaran` VARCHAR(20) NULL');
            }

            if (Schema::hasColumn('kelas', 'semester')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `semester` VARCHAR(20) NULL');
            }

            if (Schema::hasColumn('kelas', 'ruangan')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `ruangan` VARCHAR(50) NULL');
            }

            if (Schema::hasColumn('kelas', 'kapasitas')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `kapasitas` INT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to NOT NULL (optional)
        if (Schema::hasTable('kelas')) {
            if (Schema::hasColumn('kelas', 'mata_kuliah_id')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `mata_kuliah_id` BIGINT UNSIGNED NOT NULL');
            }
            
            if (Schema::hasColumn('kelas', 'dosen_id')) {
                DB::statement('ALTER TABLE `kelas` MODIFY `dosen_id` BIGINT UNSIGNED NOT NULL');
            }
        }
    }
};
