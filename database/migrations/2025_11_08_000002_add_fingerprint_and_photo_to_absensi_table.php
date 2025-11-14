<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Field untuk foto absensi yang diambil otomatis oleh kamera
            $table->string('foto_absensi')->nullable()->after('waktu_absen')->comment('Path foto yang diambil kamera saat absensi');
            
            // Field untuk data fingerprint (hash/template ID)
            $table->string('fingerprint_hash')->nullable()->after('foto_absensi')->comment('Hash fingerprint yang digunakan untuk verifikasi');
            
            // Field untuk device yang digunakan
            $table->unsignedBigInteger('device_id')->nullable()->after('fingerprint_hash')->comment('Device yang digunakan untuk absensi');
            
            // Field untuk metode verifikasi
            $table->enum('verification_method', ['fingerprint', 'camera', 'hybrid', 'manual'])->default('manual')->after('device_id')->comment('Metode verifikasi absensi');
            
            // Field untuk confidence score (tingkat kepercayaan verifikasi)
            $table->decimal('confidence_score', 5, 2)->nullable()->after('verification_method')->comment('Skor kepercayaan verifikasi (0-100)');
            
            // Foreign key ke devices
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
            
            // Index untuk performa
            $table->index('device_id');
            $table->index('verification_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropIndex(['device_id']);
            $table->dropIndex(['verification_method']);
            $table->dropColumn([
                'foto_absensi',
                'fingerprint_hash',
                'device_id',
                'verification_method',
                'confidence_score'
            ]);
        });
    }
};
