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
        // Backup data mahasiswa jika ada
        if (Schema::hasTable('mahasiswa')) {
            DB::statement('CREATE TABLE IF NOT EXISTS mahasiswa_backup AS SELECT * FROM mahasiswa');
        }

        // Drop tabel lama
        Schema::dropIfExists('mahasiswa_biometrik');
        Schema::dropIfExists('kelas_members');
        Schema::dropIfExists('mahasiswa');

        // Buat tabel mahasiswa baru
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable()->unique()->comment('FK ke users.id (NULLABLE)');
            $table->string('nim', 32)->unique();
            $table->string('nama', 150);
            $table->string('email', 150)->nullable();
            $table->string('no_hp', 32)->nullable();
            $table->unsignedBigInteger('id_prodi')->comment('FK ke program_studi.id');
            $table->unsignedBigInteger('id_kelas')->nullable()->comment('FK ke kelas.id (opsional)');
            $table->year('angkatan');
            $table->enum('status_akademik', ['aktif', 'cuti', 'lulus', 'nonaktif', 'do'])->default('aktif');
            $table->string('foto_path')->nullable();
            $table->boolean('fp_enrolled')->default(0)->comment('Fingerprint enrolled');
            $table->boolean('face_enrolled')->default(0)->comment('Face enrolled');
            $table->dateTime('last_enrolled_at')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('nim');
            $table->index('id_prodi');
            $table->index('id_kelas');
            $table->index('angkatan');
            $table->index('status_akademik');

            // Foreign keys
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
            $table->foreign('id_prodi')->references('id')->on('program_studi')->onDelete('restrict');
            $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('set null');
        });

        // Buat tabel kelas_members
        Schema::create('kelas_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_kelas');
            $table->string('nim', 32);
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('id_kelas');
            $table->index('nim');
            $table->index('tanggal_masuk');
            $table->unique(['id_kelas', 'nim', 'tanggal_keluar'], 'uniq_active_member');

            $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });

        // Buat tabel mahasiswa_biometrik
        Schema::create('mahasiswa_biometrik', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 32);
            $table->enum('tipe', ['fingerprint', 'face']);
            $table->string('ext_ref', 64)->nullable()->comment('ID template di sensor/alat');
            $table->string('template_path')->nullable()->comment('Path file template fingerprint');
            $table->string('face_embedding_path')->nullable()->comment('Path embedding face');
            $table->integer('quality_score')->nullable()->comment('Skor kualitas template');
            $table->dateTime('enrolled_at');
            $table->dateTime('revoked_at')->nullable()->comment('Tanggal dicabut/diganti');

            $table->index('nim');
            $table->index('tipe');
            $table->index('enrolled_at');
            $table->unique(['nim', 'tipe', 'ext_ref'], 'uniq_biometric');

            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_biometrik');
        Schema::dropIfExists('kelas_members');
        Schema::dropIfExists('mahasiswa');
    }
};
