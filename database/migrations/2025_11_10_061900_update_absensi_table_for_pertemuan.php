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
        Schema::table('absensi', function (Blueprint $table) {
            // Add pertemuan relation (only if not exists)
            if (!Schema::hasColumn('absensi', 'id_pertemuan')) {
                $table->unsignedBigInteger('id_pertemuan')->nullable()->after('id')->comment('FK ke pertemuan');
                // Foreign key and indexes only when column just added
                $table->foreign('id_pertemuan')->references('id')->on('pertemuan')->onDelete('cascade');
                $table->index('id_pertemuan');
                $table->index(['id_pertemuan', 'id_mahasiswa']);
            }

            // Enhanced fields for biometric verification (guard each column)
            if (!Schema::hasColumn('absensi', 'device_id')) {
                $table->string('device_id', 50)->nullable()->after('status')->comment('ID device yang digunakan scan');
                $table->index('device_id');
            }
            if (!Schema::hasColumn('absensi', 'foto_path')) {
                $table->string('foto_path')->nullable()->after('status')->comment('Path foto verifikasi (jika wajah wajib)');
            }
            if (!Schema::hasColumn('absensi', 'confidence')) {
                $table->decimal('confidence', 5, 2)->nullable()->after('status')->comment('Confidence score biometric (0-100)');
            }
            if (!Schema::hasColumn('absensi', 'verified_by')) {
                $table->enum('verified_by', ['device', 'manual', 'system'])->default('device')->after('status')->comment('Cara verifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['id_pertemuan']);
            $table->dropIndex(['id_pertemuan', 'id_mahasiswa']);
            $table->dropIndex(['device_id']);
            $table->dropIndex(['id_pertemuan']);
            $table->dropColumn(['id_pertemuan', 'device_id', 'foto_path', 'confidence', 'verified_by']);
        });
    }
};
