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
        Schema::create('sesi_absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('jadwal_kelas_id')->nullable(); // Opsional, jika dari jadwal
            $table->date('tanggal'); // Tanggal sesi absensi
            $table->string('topik')->nullable(); // Topik perkuliahan
            $table->integer('pertemuan_ke')->nullable(); // Pertemuan ke-
            $table->datetime('waktu_mulai'); // Waktu mulai absensi dibuka
            $table->datetime('waktu_selesai'); // Waktu selesai absensi ditutup
            $table->string('kode_absensi', 10)->nullable()->unique(); // Kode unik untuk absensi (e.g., ABC123)
            $table->enum('metode', ['manual', 'qr_code', 'geolocation'])->default('manual');
            $table->decimal('latitude', 10, 8)->nullable(); // Untuk geolocation
            $table->decimal('longitude', 11, 8)->nullable(); // Untuk geolocation
            $table->integer('radius_meter')->nullable(); // Radius dalam meter untuk geolocation
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('jadwal_kelas_id')->references('id')->on('jadwal_kelas')->onDelete('set null');

            // Index untuk performa
            $table->index('tanggal');
            $table->index('status');
            $table->index('kode_absensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_absensi');
    }
};
