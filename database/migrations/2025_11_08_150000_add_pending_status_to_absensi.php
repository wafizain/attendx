<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum status di tabel absensi untuk menambahkan 'pending'
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('pending', 'hadir', 'izin', 'sakit', 'alpha') DEFAULT 'alpha'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha') DEFAULT 'alpha'");
    }
};
