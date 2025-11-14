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
        // Skip migration ini - sudah diganti dengan yang lebih aman
        return;
        // 1. Update tabel users untuk password sementara
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'password_temp')) {
                    $table->string('password_temp')->nullable()->after('password');
                }
                if (!Schema::hasColumn('users', 'password_temp_expires_at')) {
                    $table->dateTime('password_temp_expires_at')->nullable()->after('password_temp');
                }
                if (!Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(0)->after('password_temp_expires_at');
                }
            });
        }

        // 2. Buat/update tabel dosen
        if (!Schema::hasTable('dosen')) {
            Schema::create('dosen', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_user')->unique()->comment('FK ke users.id');
                $table->string('nidn', 20)->unique()->nullable()->comment('Nomor Induk Dosen Nasional');
                $table->string('nip', 30)->unique()->nullable()->comment('NIP instansi');
                $table->string('nama', 150)->comment('Nama lengkap dosen');
                $table->string('gelar_depan', 30)->nullable();
                $table->string('gelar_belakang', 50)->nullable()->comment('S.T., M.Kom, Dr., dsb');
                $table->enum('pendidikan_terakhir', ['S1', 'S2', 'S3', 'Profesi', 'Sp-1', 'Sp-2'])->nullable();
                $table->string('email_kampus', 150)->nullable();
                $table->string('no_hp', 32)->nullable();
                $table->json('bidang_keahlian')->nullable()->comment('Array keahlian');
                $table->string('foto_path')->nullable();
                $table->string('ttd_path')->nullable()->comment('Tanda tangan digital');
                $table->enum('status_pegawai', ['Tetap', 'Kontrak', 'LB'])->default('LB');
                $table->boolean('status_aktif')->default(1);
                $table->text('alamat')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Foreign key
                $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
                
                // Indexes
                $table->index('nidn');
                $table->index('nip');
                $table->index('status_aktif');
            });
        } else {
            // Update existing table
            Schema::table('dosen', function (Blueprint $table) {
                if (!Schema::hasColumn('dosen', 'nama')) {
                    $table->string('nama', 150)->after('id_user');
                }
                if (!Schema::hasColumn('dosen', 'gelar_depan')) {
                    $table->string('gelar_depan', 30)->nullable()->after('nama');
                }
                if (!Schema::hasColumn('dosen', 'gelar_belakang')) {
                    $table->string('gelar_belakang', 50)->nullable()->after('gelar_depan');
                }
                if (!Schema::hasColumn('dosen', 'pendidikan_terakhir')) {
                    DB::statement("ALTER TABLE `dosen` ADD `pendidikan_terakhir` ENUM('S1','S2','S3','Profesi','Sp-1','Sp-2') NULL AFTER `gelar_belakang`");
                }
                if (!Schema::hasColumn('dosen', 'email_kampus')) {
                    $table->string('email_kampus', 150)->nullable()->after('pendidikan_terakhir');
                }
                if (!Schema::hasColumn('dosen', 'no_hp')) {
                    $table->string('no_hp', 32)->nullable()->after('email_kampus');
                }
                if (!Schema::hasColumn('dosen', 'bidang_keahlian')) {
                    $table->json('bidang_keahlian')->nullable()->after('no_hp');
                }
                if (!Schema::hasColumn('dosen', 'foto_path')) {
                    $table->string('foto_path')->nullable()->after('bidang_keahlian');
                }
                if (!Schema::hasColumn('dosen', 'ttd_path')) {
                    $table->string('ttd_path')->nullable()->after('foto_path');
                }
                if (!Schema::hasColumn('dosen', 'status_pegawai')) {
                    DB::statement("ALTER TABLE `dosen` ADD `status_pegawai` ENUM('Tetap','Kontrak','LB') DEFAULT 'LB' AFTER `ttd_path`");
                }
                if (!Schema::hasColumn('dosen', 'status_aktif')) {
                    $table->boolean('status_aktif')->default(1)->after('status_pegawai');
                }
                if (!Schema::hasColumn('dosen', 'alamat')) {
                    $table->text('alamat')->nullable()->after('status_aktif');
                }
                if (!Schema::hasColumn('dosen', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 3. Buat tabel view_password_logs
        if (!Schema::hasTable('view_password_logs')) {
            Schema::create('view_password_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->comment('Admin yang melihat');
                $table->unsignedBigInteger('dosen_user_id')->comment('Dosen yang dilihat passwordnya');
                $table->timestamp('seen_at')->useCurrent();
                $table->string('ip', 45)->nullable();
                $table->string('reason')->nullable()->comment('Alasan melihat password');

                // Foreign keys
                $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('dosen_user_id')->references('id')->on('users')->onDelete('cascade');
                
                // Indexes
                $table->index('admin_id');
                $table->index('dosen_user_id');
                $table->index('seen_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_password_logs');
        
        if (Schema::hasTable('dosen')) {
            Schema::dropIfExists('dosen');
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['password_temp', 'password_temp_expires_at', 'must_change_password']);
            });
        }
    }
};
