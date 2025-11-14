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
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mk', 20)->unique(); // Kode mata kuliah (e.g., TIF101)
            $table->string('nama_mk'); // Nama mata kuliah
            $table->integer('sks')->default(3); // Jumlah SKS
            $table->integer('semester')->nullable(); // Semester (1-8)
            $table->text('deskripsi')->nullable(); // Deskripsi mata kuliah
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
