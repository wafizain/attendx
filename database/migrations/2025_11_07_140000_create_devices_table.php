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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50)->unique()->comment('ID unik perangkat (MAC Address / Serial)');
            $table->string('device_name', 100)->comment('Nama perangkat');
            $table->enum('device_type', ['fingerprint', 'camera', 'hybrid'])->default('hybrid')->comment('Jenis perangkat');
            $table->string('model', 50)->nullable()->comment('Model perangkat (ESP32, ESP32-CAM, dll)');
            $table->string('location', 100)->nullable()->comment('Lokasi perangkat');
            $table->string('ip_address', 45)->nullable()->comment('IP Address perangkat');
            $table->string('mac_address', 17)->nullable()->comment('MAC Address');
            $table->string('firmware_version', 20)->nullable()->comment('Versi firmware');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'error'])->default('inactive')->comment('Status perangkat');
            $table->timestamp('last_seen')->nullable()->comment('Terakhir terkoneksi');
            $table->timestamp('last_sync')->nullable()->comment('Terakhir sinkronisasi data');
            $table->text('config')->nullable()->comment('Konfigurasi perangkat (JSON)');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->timestamps();
            
            $table->index('device_id');
            $table->index('status');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
