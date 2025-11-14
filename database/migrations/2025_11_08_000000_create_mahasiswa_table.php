<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id('id_mahasiswa'); // Primary Key
            $table->unsignedBigInteger('id_user')->unique()->comment('FK ke users, 1 user hanya bisa 1 mahasiswa');
            $table->string('nim', 30)->unique(); // Nomor Induk Mahasiswa
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->string('no_hp', 20)->nullable();
            $table->string('kelas', 20);
            $table->string('prodi', 100);
            $table->integer('angkatan')->comment('Tahun masuk, misal 2025');
            $table->enum('status', ['aktif', 'cuti', 'lulus', 'dropout'])->default('aktif');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
};
