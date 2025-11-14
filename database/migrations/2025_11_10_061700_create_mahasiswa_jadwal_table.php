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
        Schema::create('mahasiswa_jadwal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa')->comment('FK ke mahasiswa');
            $table->unsignedBigInteger('id_jadwal')->comment('FK ke jadwal_kuliah');
            $table->date('tanggal_daftar')->nullable()->comment('Tanggal mahasiswa didaftarkan ke jadwal');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_mahasiswa')->references('id')->on('mahasiswa')->onDelete('cascade');
            $table->foreign('id_jadwal')->references('id')->on('jadwal_kuliah')->onDelete('cascade');
            
            // Unique constraint: satu mahasiswa hanya bisa terdaftar sekali per jadwal
            $table->unique(['id_mahasiswa', 'id_jadwal']);
            
            // Indexes
            $table->index('id_mahasiswa');
            $table->index('id_jadwal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_jadwal');
    }
};
