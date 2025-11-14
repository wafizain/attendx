<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->increments('id_dosen');
            $table->unsignedBigInteger('id_user');
            $table->string('nidn', 20)->unique();
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->string('no_hp', 20)->nullable();
            $table->string('prodi', 100);
            $table->string('jabatan_akademik', 50);
            $table->string('gelar', 50);
            $table->string('foto_profil', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('password_plain', 32)->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
