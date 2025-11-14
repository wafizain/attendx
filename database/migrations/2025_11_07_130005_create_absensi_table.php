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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sesi_absensi_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->datetime('waktu_absen')->nullable(); // Waktu mahasiswa melakukan absensi
            $table->decimal('latitude', 10, 8)->nullable(); // Lokasi absensi
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ip_address')->nullable();
            $table->string('device_info')->nullable();
            $table->text('keterangan')->nullable(); // Keterangan izin/sakit
            $table->string('bukti_file')->nullable(); // File surat izin/sakit
            $table->timestamps();

            // Foreign keys
            $table->foreign('sesi_absensi_id')->references('id')->on('sesi_absensi')->onDelete('cascade');
            $table->foreign('mahasiswa_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint: satu mahasiswa hanya bisa absen 1x per sesi
            $table->unique(['sesi_absensi_id', 'mahasiswa_id']);

            // Index
            $table->index('status');
            $table->index('waktu_absen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
