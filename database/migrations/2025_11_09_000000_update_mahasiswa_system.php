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
        // 1. Update tabel mahasiswa
        if (Schema::hasTable('mahasiswa')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                // Make id_user nullable (penting untuk cegah error)
                if (Schema::hasColumn('mahasiswa', 'id_user')) {
                    DB::statement('ALTER TABLE `mahasiswa` MODIFY `id_user` BIGINT UNSIGNED NULL');
                } else {
                    $table->unsignedBigInteger('id_user')->nullable()->after('id');
                }

                // Add/update columns
                if (!Schema::hasColumn('mahasiswa', 'nim')) {
                    $table->string('nim', 32)->unique()->after('id_user');
                }
                if (!Schema::hasColumn('mahasiswa', 'nama')) {
                    $table->string('nama', 150)->after('nim');
                }
                if (!Schema::hasColumn('mahasiswa', 'email')) {
                    $table->string('email', 150)->nullable()->after('nama');
                }
                if (!Schema::hasColumn('mahasiswa', 'no_hp')) {
                    $table->string('no_hp', 32)->nullable()->after('email');
                }
                if (!Schema::hasColumn('mahasiswa', 'id_prodi')) {
                    $table->unsignedBigInteger('id_prodi')->after('no_hp');
                }
                if (!Schema::hasColumn('mahasiswa', 'id_kelas')) {
                    $table->unsignedBigInteger('id_kelas')->nullable()->after('id_prodi');
                }
                if (!Schema::hasColumn('mahasiswa', 'angkatan')) {
                    $table->year('angkatan')->after('id_kelas');
                }
                if (!Schema::hasColumn('mahasiswa', 'status_akademik')) {
                    DB::statement("ALTER TABLE `mahasiswa` ADD `status_akademik` ENUM('aktif','cuti','lulus','nonaktif','do') DEFAULT 'aktif' AFTER `angkatan`");
                }
                if (!Schema::hasColumn('mahasiswa', 'foto_path')) {
                    $table->string('foto_path')->nullable()->after('status_akademik');
                }
                
                // Biometric enrollment flags
                if (!Schema::hasColumn('mahasiswa', 'fp_enrolled')) {
                    $table->boolean('fp_enrolled')->default(0)->after('foto_path')->comment('Fingerprint enrolled');
                }
                if (!Schema::hasColumn('mahasiswa', 'face_enrolled')) {
                    $table->boolean('face_enrolled')->default(0)->after('fp_enrolled')->comment('Face enrolled');
                }
                if (!Schema::hasColumn('mahasiswa', 'last_enrolled_at')) {
                    $table->dateTime('last_enrolled_at')->nullable()->after('face_enrolled');
                }
                
                if (!Schema::hasColumn('mahasiswa', 'alamat')) {
                    $table->text('alamat')->nullable()->after('last_enrolled_at');
                }
                if (!Schema::hasColumn('mahasiswa', 'deleted_at')) {
                    $table->softDeletes();
                }

                // Foreign keys
                if (!Schema::hasColumn('mahasiswa', 'id_user')) {
                    $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('mahasiswa', 'id_prodi')) {
                    $table->foreign('id_prodi')->references('id')->on('program_studi')->onDelete('restrict');
                }
                if (!Schema::hasColumn('mahasiswa', 'id_kelas')) {
                    $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('set null');
                }

                // Indexes
                $table->index('nim');
                $table->index('id_prodi');
                $table->index('angkatan');
                $table->index('status_akademik');
            });
        } else {
            // Create new table
            Schema::create('mahasiswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_user')->nullable()->unique()->comment('FK ke users.id (nullable)');
                $table->string('nim', 32)->unique();
                $table->string('nama', 150);
                $table->string('email', 150)->nullable();
                $table->string('no_hp', 32)->nullable();
                $table->unsignedBigInteger('id_prodi')->comment('FK ke program_studi.id');
                $table->unsignedBigInteger('id_kelas')->nullable()->comment('FK ke kelas.id (opsional)');
                $table->year('angkatan');
                $table->enum('status_akademik', ['aktif', 'cuti', 'lulus', 'nonaktif', 'do'])->default('aktif');
                $table->string('foto_path')->nullable();
                
                // Biometric flags
                $table->boolean('fp_enrolled')->default(0)->comment('Fingerprint enrolled');
                $table->boolean('face_enrolled')->default(0)->comment('Face enrolled');
                $table->dateTime('last_enrolled_at')->nullable();
                
                $table->text('alamat')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Foreign keys
                $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
                $table->foreign('id_prodi')->references('id')->on('program_studi')->onDelete('restrict');
                $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('set null');

                // Indexes
                $table->index('nim');
                $table->index('id_prodi');
                $table->index('angkatan');
                $table->index('status_akademik');
            });
        }

        // 2. Buat tabel mahasiswa_biometrik
        if (!Schema::hasTable('mahasiswa_biometrik')) {
            Schema::create('mahasiswa_biometrik', function (Blueprint $table) {
                $table->id();
                $table->string('nim', 32)->comment('Refer ke mahasiswa.nim');
                $table->enum('tipe', ['fingerprint', 'face']);
                $table->string('ext_ref', 64)->nullable()->comment('ID template di sensor/alat');
                $table->string('template_path')->nullable()->comment('Path file template fingerprint');
                $table->string('face_embedding_path')->nullable()->comment('Path embedding face');
                $table->integer('quality_score')->nullable()->comment('Skor kualitas template');
                $table->dateTime('enrolled_at');
                $table->dateTime('revoked_at')->nullable()->comment('Tanggal dicabut/diganti');
                
                // Foreign key
                $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
                
                // Unique constraint
                $table->unique(['nim', 'tipe', 'ext_ref'], 'uniq_biometric');
                
                // Indexes
                $table->index('nim');
                $table->index('tipe');
                $table->index('enrolled_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_biometrik');
        
        if (Schema::hasTable('mahasiswa')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropForeign(['id_user']);
                $table->dropForeign(['id_prodi']);
                $table->dropForeign(['id_kelas']);
            });
        }
    }
};
