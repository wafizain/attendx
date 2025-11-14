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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedBigInteger('dosen_id'); // ID dosen pengampu
            $table->string('nama_kelas'); // e.g., TIF-A, TIF-B
            $table->string('tahun_ajaran', 20); // e.g., 2024/2025
            $table->enum('semester', ['ganjil', 'genap']); // Semester ganjil/genap
            $table->string('ruangan')->nullable(); // Ruang kelas
            $table->integer('kapasitas')->default(40); // Kapasitas mahasiswa
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            // Foreign keys
            $table->foreign('mata_kuliah_id')->references('id')->on('mata_kuliah')->onDelete('cascade');
            $table->foreign('dosen_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
