<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran', 20); // e.g., "2024/2025"
            $table->tinyInteger('semester'); // 1 or 2
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('tidak_aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->unique(['tahun_ajaran', 'semester']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('semesters');
    }
};
