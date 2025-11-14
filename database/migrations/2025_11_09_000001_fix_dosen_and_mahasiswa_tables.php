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
        // 1. Fix tabel dosen - tambahkan kolom yang belum ada
        if (Schema::hasTable('dosen')) {
            // Cek dan tambahkan kolom satu per satu
            if (!Schema::hasColumn('dosen', 'gelar_depan')) {
                DB::statement('ALTER TABLE `dosen` ADD `gelar_depan` VARCHAR(30) NULL AFTER `nama`');
            }
            if (!Schema::hasColumn('dosen', 'gelar_belakang')) {
                DB::statement('ALTER TABLE `dosen` ADD `gelar_belakang` VARCHAR(50) NULL AFTER `gelar_depan`');
            }
            if (!Schema::hasColumn('dosen', 'pendidikan_terakhir')) {
                DB::statement("ALTER TABLE `dosen` ADD `pendidikan_terakhir` ENUM('S1','S2','S3','Profesi','Sp-1','Sp-2') NULL AFTER `gelar_belakang`");
            }
            if (!Schema::hasColumn('dosen', 'email_kampus')) {
                DB::statement('ALTER TABLE `dosen` ADD `email_kampus` VARCHAR(150) NULL AFTER `pendidikan_terakhir`');
            }
            if (!Schema::hasColumn('dosen', 'bidang_keahlian')) {
                DB::statement('ALTER TABLE `dosen` ADD `bidang_keahlian` JSON NULL AFTER `no_hp`');
            }
            if (!Schema::hasColumn('dosen', 'foto_path')) {
                DB::statement('ALTER TABLE `dosen` ADD `foto_path` VARCHAR(255) NULL AFTER `bidang_keahlian`');
            }
            if (!Schema::hasColumn('dosen', 'ttd_path')) {
                DB::statement('ALTER TABLE `dosen` ADD `ttd_path` VARCHAR(255) NULL AFTER `foto_path`');
            }
            if (!Schema::hasColumn('dosen', 'status_pegawai')) {
                DB::statement("ALTER TABLE `dosen` ADD `status_pegawai` ENUM('Tetap','Kontrak','LB') DEFAULT 'LB' AFTER `ttd_path`");
            }
            if (!Schema::hasColumn('dosen', 'status_aktif')) {
                DB::statement('ALTER TABLE `dosen` ADD `status_aktif` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status_pegawai`');
            }
            if (!Schema::hasColumn('dosen', 'alamat')) {
                DB::statement('ALTER TABLE `dosen` ADD `alamat` TEXT NULL AFTER `status_aktif`');
            }
            if (!Schema::hasColumn('dosen', 'deleted_at')) {
                DB::statement('ALTER TABLE `dosen` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`');
            }
        }

        // 2. Fix tabel mahasiswa - tambahkan kolom yang belum ada
        if (Schema::hasTable('mahasiswa')) {
            // Pastikan id_user nullable
            $columns = DB::select("SHOW COLUMNS FROM mahasiswa WHERE Field = 'id_user'");
            if (!empty($columns)) {
                $column = $columns[0];
                if (strpos($column->Type, 'unsigned') !== false && $column->Null === 'NO') {
                    DB::statement('ALTER TABLE `mahasiswa` MODIFY `id_user` BIGINT UNSIGNED NULL');
                }
            }

            // Tambahkan kolom yang belum ada
            if (!Schema::hasColumn('mahasiswa', 'foto_path')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `foto_path` VARCHAR(255) NULL AFTER `status_akademik`');
            }
            if (!Schema::hasColumn('mahasiswa', 'fp_enrolled')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `fp_enrolled` TINYINT(1) DEFAULT 0 AFTER `foto_path`');
            }
            if (!Schema::hasColumn('mahasiswa', 'face_enrolled')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `face_enrolled` TINYINT(1) DEFAULT 0 AFTER `fp_enrolled`');
            }
            if (!Schema::hasColumn('mahasiswa', 'last_enrolled_at')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `last_enrolled_at` DATETIME NULL AFTER `face_enrolled`');
            }
            if (!Schema::hasColumn('mahasiswa', 'deleted_at')) {
                DB::statement('ALTER TABLE `mahasiswa` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`');
            }
        }

        // 3. Buat tabel mahasiswa_biometrik jika belum ada
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
        // Optional: rollback changes
    }
};
