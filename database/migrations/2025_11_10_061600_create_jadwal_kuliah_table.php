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
        Schema::create('jadwal_kuliah', function (Blueprint $table) {
            $table->id();
            
            // Relasi
            $table->unsignedBigInteger('id_mk')->comment('FK ke mata_kuliah');
            $table->unsignedBigInteger('id_dosen')->comment('FK ke users (role dosen)');
            $table->unsignedBigInteger('id_kelas')->nullable()->comment('FK ke kelas (opsional, referensi administratif)');
            $table->unsignedBigInteger('id_ruangan')->comment('FK ke ruangan');
            
            // Waktu berulang
            $table->tinyInteger('hari')->comment('1=Senin, 2=Selasa, ..., 7=Minggu');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            
            // Periode/Semester
            $table->date('tanggal_mulai')->comment('Awal periode jadwal');
            $table->date('tanggal_selesai')->comment('Akhir periode jadwal');
            
            // Kode paralel
            $table->string('paralel', 5)->nullable()->comment('Kode paralel: A, B, C, dst');
            
            // Aturan absensi (override per jadwal)
            $table->integer('absen_open_min')->default(10)->comment('Buka X menit sebelum jam_mulai');
            $table->integer('absen_close_min')->default(30)->comment('Tutup X menit setelah jam_mulai');
            $table->integer('grace_late_min')->default(15)->comment('Telat jika lewat X menit dari jam_mulai');
            $table->boolean('wajah_wajib')->default(false)->comment('Wajib foto saat verifikasi fingerprint');
            
            // Status & catatan
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('catatan')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_mk')->references('id')->on('mata_kuliah')->onDelete('cascade');
            $table->foreign('id_dosen')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('set null');
            $table->foreign('id_ruangan')->references('id')->on('ruangan')->onDelete('cascade');
            
            // Indexes untuk performa
            $table->index('id_dosen');
            $table->index('id_ruangan');
            $table->index(['hari', 'jam_mulai']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index('status');
            
            // Unique constraint untuk mencegah tabrakan ruang
            // (ruangan, hari, jam) dalam periode yang sama
            $table->unique(['id_ruangan', 'hari', 'jam_mulai', 'tanggal_mulai', 'tanggal_selesai'], 'unique_ruangan_waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kuliah');
    }
};
