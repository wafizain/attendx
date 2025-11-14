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
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_jadwal')->comment('FK ke jadwal_kuliah');
            $table->tinyInteger('minggu_ke')->comment('Minggu ke-N (1-14 atau lebih)');
            $table->date('tanggal')->comment('Tanggal pertemuan');
            $table->time('jam_mulai')->comment('Jam mulai pertemuan (bisa override dari jadwal)');
            $table->time('jam_selesai')->comment('Jam selesai pertemuan');
            $table->unsignedBigInteger('id_ruangan')->comment('FK ke ruangan (bisa override)');
            
            // Status sesi
            $table->enum('status_sesi', ['direncanakan', 'berjalan', 'selesai', 'dibatalkan'])->default('direncanakan');
            
            // Tracking pembukaan/penutupan
            $table->unsignedBigInteger('dibuka_oleh')->nullable()->comment('FK ke users (dosen/admin yang buka)');
            $table->timestamp('dibuka_pada')->nullable()->comment('Waktu sesi dibuka');
            $table->timestamp('ditutup_pada')->nullable()->comment('Waktu sesi ditutup');
            
            // Materi & catatan
            $table->string('materi', 200)->nullable()->comment('Topik/materi pertemuan');
            $table->text('catatan')->nullable()->comment('Catatan pertemuan');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_jadwal')->references('id')->on('jadwal_kuliah')->onDelete('cascade');
            $table->foreign('id_ruangan')->references('id')->on('ruangan')->onDelete('cascade');
            $table->foreign('dibuka_oleh')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('id_jadwal');
            $table->index('tanggal');
            $table->index('status_sesi');
            $table->index(['id_ruangan', 'tanggal', 'jam_mulai']);
            
            // Unique constraint: satu jadwal tidak boleh punya dua pertemuan di minggu yang sama
            $table->unique(['id_jadwal', 'minggu_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
