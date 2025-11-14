<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelas_members')) {
            return;
        }

        Schema::table('kelas_members', function (Blueprint $table) {
            // Drop existing FK on nim if present, then recreate with ON UPDATE CASCADE
            try {
                $table->dropForeign(['nim']);
            } catch (\Throwable $e) {
                // ignore if not exists
            }

            $table->foreign('nim')
                ->references('nim')
                ->on('mahasiswa')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('kelas_members')) {
            return;
        }

        Schema::table('kelas_members', function (Blueprint $table) {
            try {
                $table->dropForeign(['nim']);
            } catch (\Throwable $e) {
                // ignore
            }

            // Recreate FK without ON UPDATE CASCADE (original behavior)
            $table->foreign('nim')
                ->references('nim')
                ->on('mahasiswa')
                ->onDelete('cascade');
        });
    }
};
