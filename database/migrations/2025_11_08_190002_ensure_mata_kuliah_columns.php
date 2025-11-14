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
        if (Schema::hasTable('mata_kuliah')) {
            // Add id_prodi if not exists
            if (!Schema::hasColumn('mata_kuliah', 'id_prodi')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `id_prodi` BIGINT UNSIGNED NULL AFTER `id`');
            }

            // Add kurikulum if not exists
            if (!Schema::hasColumn('mata_kuliah', 'kurikulum')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `kurikulum` VARCHAR(20) NOT NULL DEFAULT "2024" AFTER `id_prodi`');
            }

            // Add jenis if not exists
            if (!Schema::hasColumn('mata_kuliah', 'jenis')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `jenis` ENUM("Teori","Praktikum","Teori+Praktikum") NOT NULL DEFAULT "Teori" AFTER `sks`');
            }

            // Add semester_rekomendasi if not exists
            if (!Schema::hasColumn('mata_kuliah', 'semester_rekomendasi')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `semester_rekomendasi` TINYINT NULL AFTER `jenis`');
            }

            // Add prasyarat if not exists
            if (!Schema::hasColumn('mata_kuliah', 'prasyarat')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `prasyarat` JSON NULL AFTER `deskripsi`');
            }

            // Add status if not exists (make it boolean)
            if (!Schema::hasColumn('mata_kuliah', 'status')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `status` TINYINT(1) NOT NULL DEFAULT 1 AFTER `prasyarat`');
            } else {
                // Update existing status column to boolean if it's not
                DB::statement('ALTER TABLE `mata_kuliah` MODIFY `status` TINYINT(1) NOT NULL DEFAULT 1');
            }

            // Add kode_eksternal if not exists
            if (!Schema::hasColumn('mata_kuliah', 'kode_eksternal')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `kode_eksternal` VARCHAR(32) NULL AFTER `status`');
            }

            // Add deleted_at if not exists (soft delete)
            if (!Schema::hasColumn('mata_kuliah', 'deleted_at')) {
                DB::statement('ALTER TABLE `mata_kuliah` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: rollback changes
    }
};
