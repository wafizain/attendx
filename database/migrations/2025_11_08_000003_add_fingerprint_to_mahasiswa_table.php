<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            // Field untuk foto profil mahasiswa
            $table->string('foto_profil')->nullable()->after('email')->comment('Path foto profil mahasiswa');
            
            // Field untuk template sidik jari (bisa multiple fingers)
            $table->text('fingerprint_template_1')->nullable()->after('foto_profil')->comment('Template sidik jari jari 1 (biasanya jempol kanan)');
            $table->text('fingerprint_template_2')->nullable()->after('fingerprint_template_1')->comment('Template sidik jari jari 2 (backup)');
            
            // Field untuk fingerprint ID dari sensor
            $table->integer('fingerprint_id_1')->nullable()->after('fingerprint_template_2')->comment('ID fingerprint di sensor untuk jari 1');
            $table->integer('fingerprint_id_2')->nullable()->after('fingerprint_id_1')->comment('ID fingerprint di sensor untuk jari 2');
            
            // Field untuk status registrasi fingerprint
            $table->boolean('fingerprint_registered')->default(false)->after('fingerprint_id_2')->comment('Status apakah sudah registrasi fingerprint');
            $table->timestamp('fingerprint_registered_at')->nullable()->after('fingerprint_registered')->comment('Waktu registrasi fingerprint');
            
            // Field untuk device yang digunakan saat registrasi
            $table->unsignedBigInteger('registered_device_id')->nullable()->after('fingerprint_registered_at')->comment('Device yang digunakan untuk registrasi');
            
            // Foreign key
            $table->foreign('registered_device_id')->references('id')->on('devices')->onDelete('set null');
            
            // Index
            $table->index('fingerprint_registered');
            $table->index('fingerprint_id_1');
            $table->index('fingerprint_id_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['registered_device_id']);
            $table->dropIndex(['fingerprint_registered']);
            $table->dropIndex(['fingerprint_id_1']);
            $table->dropIndex(['fingerprint_id_2']);
            $table->dropColumn([
                'foto_profil',
                'fingerprint_template_1',
                'fingerprint_template_2',
                'fingerprint_id_1',
                'fingerprint_id_2',
                'fingerprint_registered',
                'fingerprint_registered_at',
                'registered_device_id'
            ]);
        });
    }
};
