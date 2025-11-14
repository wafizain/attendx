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
        Schema::create('ruangan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique()->comment('Kode ruangan unik');
            $table->string('nama', 100)->comment('Nama ruangan');
            $table->integer('kapasitas')->default(0)->comment('Kapasitas maksimal');
            $table->string('lokasi', 150)->nullable()->comment('Lokasi/gedung ruangan');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('kode');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruangan');
    }
};
