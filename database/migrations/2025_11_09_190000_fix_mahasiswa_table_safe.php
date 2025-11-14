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
        // Fix tabel mahasiswa dengan pengecekan kolom
        if (Schema::hasTable('mahasiswa')) {
            // 1. Pastikan id_user nullable
            DB::statement('ALTER TABLE `mahasiswa` MODIFY `id_user` BIGINT UNSIGNED NULL');

            // 2. Tambahkan kolom status_akademik jika belum ada
            if (!Schema::hasColumn('mahasiswa', 'status_akademik')) {
                DB::statement("ALTER TABLE `mahasiswa` ADD `status_akademik` ENUM('aktif','cuti','lulus','nonaktif','do') DEFAULT 'aktif' AFTER `angkatan`");
            }

            // 3. Tambahkan kolom foto_path
            if (!Schema::hasColumn('mahasiswa', 'foto_path')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `foto_path` VARCHAR(255) NULL AFTER `status_akademik`');
            }

            // 4. Tambahkan kolom biometrik
            if (!Schema::hasColumn('mahasiswa', 'fp_enrolled')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `fp_enrolled` TINYINT(1) DEFAULT 0 AFTER `foto_path`');
            }
            if (!Schema::hasColumn('mahasiswa', 'face_enrolled')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `face_enrolled` TINYINT(1) DEFAULT 0 AFTER `fp_enrolled`');
            }
            if (!Schema::hasColumn('mahasiswa', 'last_enrolled_at')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `last_enrolled_at` DATETIME NULL AFTER `face_enrolled`');
            }

            // 5. Tambahkan kolom alamat
            if (!Schema::hasColumn('mahasiswa', 'alamat')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `alamat` TEXT NULL AFTER `last_enrolled_at`');
            }

            // 6. Tambahkan kolom deleted_at
            if (!Schema::hasColumn('mahasiswa', 'deleted_at')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`');
            }

            // 7. Fix kolom 'kelas' lama jika ada (buat nullable atau hapus)
            if (Schema::hasColumn('mahasiswa', 'kelas')) {
                // Buat nullable dulu (aman)
                DB::statement('ALTER TABLE `mahasiswa` MODIFY `kelas` VARCHAR(50) NULL');
                // Atau hapus jika tidak dipakai
                // DB::statement('ALTER TABLE `mahasiswa` DROP COLUMN `kelas`');
            }
        }

        // 7. Buat tabel mahasiswa_biometrik
        if (!Schema::hasTable('mahasiswa_biometrik')) {
            Schema::create('mahasiswa_biometrik', function (Blueprint $table) {
                $table->id();
                $table->string('nim', 32);
                $table->enum('tipe', ['fingerprint', 'face']);
                $table->string('ext_ref', 64)->nullable();
                $table->string('template_path')->nullable();
                $table->string('face_embedding_path')->nullable();
                $table->integer('quality_score')->nullable();
                $table->dateTime('enrolled_at');
                $table->dateTime('revoked_at')->nullable();
                
                $table->index('nim');
                $table->index('tipe');
                $table->index('enrolled_at');
                $table->unique(['nim', 'tipe', 'ext_ref'], 'uniq_biometric');
            });
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
