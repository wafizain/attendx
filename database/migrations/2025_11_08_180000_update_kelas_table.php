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
        // Update tabel kelas
        if (Schema::hasTable('kelas')) {
            Schema::table('kelas', function (Blueprint $table) {
                // Tambah kolom baru jika belum ada
                if (!Schema::hasColumn('kelas', 'kode')) {
                    $table->string('kode', 24)->after('id');
                }
                if (!Schema::hasColumn('kelas', 'nama')) {
                    $table->string('nama', 100)->after('kode');
                }
                if (!Schema::hasColumn('kelas', 'angkatan')) {
                    $table->year('angkatan')->after('prodi_id');
                }
                if (!Schema::hasColumn('kelas', 'paralel')) {
                    $table->string('paralel', 8)->nullable()->after('angkatan');
                }
                if (!Schema::hasColumn('kelas', 'semester_aktif')) {
                    $table->tinyInteger('semester_aktif')->nullable()->after('paralel');
                }
                if (!Schema::hasColumn('kelas', 'wali_dosen_id')) {
                    $table->unsignedBigInteger('wali_dosen_id')->nullable()->after('dosen_id');
                }
                if (!Schema::hasColumn('kelas', 'kapasitas')) {
                    $table->integer('kapasitas')->nullable()->after('wali_dosen_id');
                }
                if (!Schema::hasColumn('kelas', 'ruangan_default')) {
                    $table->string('ruangan_default', 50)->nullable()->after('kapasitas');
                }
                if (!Schema::hasColumn('kelas', 'catatan')) {
                    $table->text('catatan')->nullable()->after('status');
                }
                if (!Schema::hasColumn('kelas', 'deleted_at')) {
                    $table->softDeletes();
                }

                // Add unique constraint if not exists
                // Note: Laravel will skip if index already exists
                try {
                    $table->unique(['prodi_id', 'angkatan', 'kode'], 'uniq_kelas');
                } catch (\Exception $e) {
                    // Index already exists, skip
                }
                
                // Add foreign key for wali_dosen_id
                $table->foreign('wali_dosen_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Buat tabel kelas_members
        if (!Schema::hasTable('kelas_members')) {
            Schema::create('kelas_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_kelas')->comment('FK ke kelas.id');
                $table->string('nim', 32)->comment('NIM mahasiswa');
                $table->date('tanggal_masuk')->comment('Tanggal bergabung ke kelas');
                $table->date('tanggal_keluar')->nullable()->comment('NULL = masih aktif');
                $table->string('keterangan', 150)->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('cascade');
                
                // Unique constraint: cegah dobel aktif (nim yang sama di kelas yang sama dengan tanggal_keluar NULL)
                $table->unique(['id_kelas', 'nim', 'tanggal_keluar'], 'uniq_active_member');
                
                // Indexes
                $table->index('nim');
                $table->index('tanggal_masuk');
                $table->index('tanggal_keluar');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_members');

        if (Schema::hasTable('kelas')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropUnique('uniq_kelas');
                $table->dropForeign(['wali_dosen_id']);
                
                $table->dropColumn([
                    'kode',
                    'angkatan',
                    'paralel',
                    'semester_aktif',
                    'wali_dosen_id',
                    'kapasitas',
                    'ruangan_default',
                    'catatan',
                    'deleted_at'
                ]);
            });
        }
    }
};
