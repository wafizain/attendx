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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // Jenis aksi: login, logout, create, update, delete, view, dll
            $table->string('module')->nullable(); // Module: admin, dosen, mahasiswa, absensi, dll
            $table->string('description')->nullable(); // Deskripsi detail aktivitas
            $table->string('ip_address')->nullable(); // IP Address user
            $table->string('user_agent')->nullable(); // Browser/device info
            $table->text('data')->nullable(); // Data tambahan dalam format JSON
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Index untuk performa query
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
