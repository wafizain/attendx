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
        // Update tabel mata_kuliah
        if (Schema::hasTable('mata_kuliah')) {
            Schema::table('mata_kuliah', function (Blueprint $table) {
                // Tambah kolom baru jika belum ada
                if (!Schema::hasColumn('mata_kuliah', 'id_prodi')) {
                    $table->unsignedBigInteger('id_prodi')->after('id');
                }
                if (!Schema::hasColumn('mata_kuliah', 'kurikulum')) {
                    $table->string('kurikulum', 20)->after('id_prodi')->default('2024');
                }
                if (!Schema::hasColumn('mata_kuliah', 'jenis')) {
                    $table->enum('jenis', ['Teori', 'Praktikum', 'Teori+Praktikum'])->default('Teori')->after('sks');
                }
                if (!Schema::hasColumn('mata_kuliah', 'semester_rekomendasi')) {
                    $table->tinyInteger('semester_rekomendasi')->nullable()->after('jenis');
                }
                if (!Schema::hasColumn('mata_kuliah', 'deskripsi')) {
                    $table->text('deskripsi')->nullable()->after('semester_rekomendasi');
                }
                if (!Schema::hasColumn('mata_kuliah', 'prasyarat')) {
                    $table->json('prasyarat')->nullable()->after('deskripsi');
                }
                if (!Schema::hasColumn('mata_kuliah', 'status')) {
                    $table->boolean('status')->default(1)->after('prasyarat');
                }
                if (!Schema::hasColumn('mata_kuliah', 'kode_eksternal')) {
                    $table->string('kode_eksternal', 32)->nullable()->after('status');
                }
                if (!Schema::hasColumn('mata_kuliah', 'deleted_at')) {
                    $table->softDeletes();
                }

                // Add foreign key
                $table->foreign('id_prodi')->references('id')->on('program_studi')->onDelete('cascade');
            });

            // Add unique constraint
            DB::statement('ALTER TABLE `mata_kuliah` ADD UNIQUE `uniq_mk` (`id_prodi`, `kurikulum`, `kode_mk`)');
        }

        // Buat tabel mata_kuliah_pengampu
        if (!Schema::hasTable('mata_kuliah_pengampu')) {
            Schema::create('mata_kuliah_pengampu', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_mk')->comment('FK ke mata_kuliah.id');
                $table->unsignedBigInteger('dosen_id')->comment('FK ke users.id (dosen)');
                $table->enum('peran', ['Pengampu Utama', 'Ko-Pengampu', 'Asisten'])->default('Pengampu Utama');
                $table->tinyInteger('bobot_persen')->nullable()->comment('Proporsi tugas/penilaian');
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_mk')->references('id')->on('mata_kuliah')->onDelete('cascade');
                $table->foreign('dosen_id')->references('id')->on('users')->onDelete('cascade');

                // Unique constraint
                $table->unique(['id_mk', 'dosen_id'], 'uniq_pengampu');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah_pengampu');

        if (Schema::hasTable('mata_kuliah')) {
            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->dropForeign(['id_prodi']);
                $table->dropUnique('uniq_mk');
                
                $table->dropColumn([
                    'id_prodi',
                    'kurikulum',
                    'jenis',
                    'semester_rekomendasi',
                    'deskripsi',
                    'prasyarat',
                    'status',
                    'kode_eksternal',
                    'deleted_at'
                ]);
            });
        }
    }
};
