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
        if (!Schema::hasTable('program_studi')) {
            Schema::create('program_studi', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 16)->unique()->comment('Kode prodi: IF-01, SI, TI-S1');
                $table->string('nama', 150)->comment('Nama program studi');
                $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3'])->default('S1');
                $table->unsignedBigInteger('fakultas_id')->nullable()->comment('FK ke tabel fakultas (opsional)');
                $table->enum('akreditasi', ['A', 'B', 'C', 'Baik', 'Baik Sekali', 'Unggul'])->nullable();
                $table->unsignedBigInteger('kaprodi_user_id')->nullable()->comment('FK ke users (dosen)');
                $table->string('email_kontak', 150)->nullable();
                $table->string('telepon_kontak', 32)->nullable();
                $table->boolean('status')->default(1)->comment('1=aktif, 0=nonaktif');
                $table->string('slug', 160)->unique()->nullable()->comment('URL-friendly slug');
                $table->string('kode_eksternal', 32)->nullable()->comment('Kode SIAKAD/Feeder/PD-Dikti');
                $table->timestamps();
                $table->softDeletes();

                // Foreign keys
                $table->foreign('kaprodi_user_id')->references('id')->on('users')->onDelete('set null');
                
                // Indexes
                $table->index('status');
                $table->index('jenjang');
                $table->index('slug');
            });
        }

        // Tambah kolom prodi_id ke tabel users jika belum ada
        if (!Schema::hasColumn('users', 'prodi_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('prodi_id')->nullable()->after('role');
                $table->foreign('prodi_id')->references('id')->on('program_studi')->onDelete('set null');
            });
        }

        // Tambah kolom prodi_id ke tabel kelas jika belum ada
        if (Schema::hasTable('kelas') && !Schema::hasColumn('kelas', 'prodi_id')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->unsignedBigInteger('prodi_id')->nullable()->after('id');
                $table->foreign('prodi_id')->references('id')->on('program_studi')->onDelete('set null');
            });
        }

        // Tambah kolom prodi_id ke tabel mata_kuliah jika belum ada
        if (Schema::hasTable('mata_kuliah') && !Schema::hasColumn('mata_kuliah', 'prodi_id')) {
            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->unsignedBigInteger('prodi_id')->nullable()->after('id');
                $table->foreign('prodi_id')->references('id')->on('program_studi')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mata_kuliah') && Schema::hasColumn('mata_kuliah', 'prodi_id')) {
            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->dropForeign(['prodi_id']);
                $table->dropColumn('prodi_id');
            });
        }

        if (Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'prodi_id')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropForeign(['prodi_id']);
                $table->dropColumn('prodi_id');
            });
        }

        if (Schema::hasColumn('users', 'prodi_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['prodi_id']);
                $table->dropColumn('prodi_id');
            });
        }

        Schema::dropIfExists('program_studi');
    }
};
