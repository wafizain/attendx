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
        // Enhance pertemuan table
        Schema::table('pertemuan', function (Blueprint $table) {
            // Add window override fields
            if (!Schema::hasColumn('pertemuan', 'open_at')) {
                $table->timestamp('open_at')->nullable()->after('id_ruangan')->comment('Override waktu buka absensi');
            }
            if (!Schema::hasColumn('pertemuan', 'late_after')) {
                $table->timestamp('late_after')->nullable()->after('open_at')->comment('Override batas telat');
            }
            if (!Schema::hasColumn('pertemuan', 'close_at')) {
                $table->timestamp('close_at')->nullable()->after('late_after')->comment('Override waktu tutup absensi');
            }
            
            // Add void reason for cancelled sessions
            if (!Schema::hasColumn('pertemuan', 'void_reason')) {
                $table->text('void_reason')->nullable()->after('catatan')->comment('Alasan pembatalan');
            }
        });

        // Enhance absensi table
        Schema::table('absensi', function (Blueprint $table) {
            // Rename/add columns to match spec
            if (!Schema::hasColumn('absensi', 'waktu_scan')) {
                if (Schema::hasColumn('absensi', 'waktu_absen')) {
                    $table->renameColumn('waktu_absen', 'waktu_scan');
                } else {
                    $table->timestamp('waktu_scan')->nullable()->after('id_pertemuan')->comment('Waktu scan absensi');
                }
            }
            
            // Ensure id_mahasiswa exists (might be named differently)
            if (!Schema::hasColumn('absensi', 'id_mahasiswa') && Schema::hasColumn('absensi', 'mahasiswa_id')) {
                $table->renameColumn('mahasiswa_id', 'id_mahasiswa');
            }
            
            // Add unique constraint for pertemuan + mahasiswa
            // Drop existing indexes first if they exist
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('absensi');
            
            if (!isset($indexesFound['absensi_unique_pertemuan_mahasiswa'])) {
                $table->unique(['id_pertemuan', 'id_mahasiswa'], 'absensi_unique_pertemuan_mahasiswa');
            }
        });

        // Add device-ruangan mapping table
        if (!Schema::hasTable('device_ruangan')) {
            Schema::create('device_ruangan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_id')->comment('FK ke devices');
                $table->unsignedBigInteger('id_ruangan')->comment('FK ke ruangan');
                $table->boolean('is_primary')->default(true)->comment('Device utama di ruangan ini');
                $table->timestamps();
                
                $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
                $table->foreign('id_ruangan')->references('id')->on('ruangan')->onDelete('cascade');
                
                $table->unique(['device_id', 'id_ruangan']);
                $table->index('id_ruangan');
            });
        }

        // Create activity log table for pertemuan
        if (!Schema::hasTable('pertemuan_log')) {
            Schema::create('pertemuan_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pertemuan')->comment('FK ke pertemuan');
                $table->unsignedBigInteger('user_id')->nullable()->comment('FK ke users');
                $table->string('action', 50)->comment('open|close|reschedule|cancel|update');
                $table->text('description')->nullable()->comment('Deskripsi aktivitas');
                $table->json('old_data')->nullable()->comment('Data sebelum perubahan');
                $table->json('new_data')->nullable()->comment('Data setelah perubahan');
                $table->timestamp('created_at')->useCurrent();
                
                $table->foreign('id_pertemuan')->references('id')->on('pertemuan')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                
                $table->index('id_pertemuan');
                $table->index('action');
                $table->index('created_at');
            });
        }

        // Create scan rate limit table
        if (!Schema::hasTable('scan_rate_limit')) {
            Schema::create('scan_rate_limit', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pertemuan');
                $table->unsignedBigInteger('id_mahasiswa');
                $table->timestamp('last_scan_at');
                $table->tinyInteger('attempt_count')->default(1);
                
                $table->foreign('id_pertemuan')->references('id')->on('pertemuan')->onDelete('cascade');
                $table->foreign('id_mahasiswa')->references('id')->on('mahasiswa')->onDelete('cascade');
                
                $table->unique(['id_pertemuan', 'id_mahasiswa']);
                $table->index('last_scan_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->dropColumn(['open_at', 'late_after', 'close_at', 'void_reason']);
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->dropUnique('absensi_unique_pertemuan_mahasiswa');
        });

        Schema::dropIfExists('scan_rate_limit');
        Schema::dropIfExists('pertemuan_log');
        Schema::dropIfExists('device_ruangan');
    }
};
