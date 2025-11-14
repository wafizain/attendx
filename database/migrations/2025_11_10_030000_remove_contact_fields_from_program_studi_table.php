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
        Schema::table('program_studi', function (Blueprint $table) {
            if (Schema::hasColumn('program_studi', 'email_kontak')) {
                $table->dropColumn('email_kontak');
            }
            if (Schema::hasColumn('program_studi', 'telepon_kontak')) {
                $table->dropColumn('telepon_kontak');
            }
            if (Schema::hasColumn('program_studi', 'kode_eksternal')) {
                $table->dropColumn('kode_eksternal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            if (!Schema::hasColumn('program_studi', 'email_kontak')) {
                $table->string('email_kontak', 150)->nullable()->after('kaprodi_user_id');
            }
            if (!Schema::hasColumn('program_studi', 'telepon_kontak')) {
                $table->string('telepon_kontak', 32)->nullable()->after('email_kontak');
            }
            if (!Schema::hasColumn('program_studi', 'kode_eksternal')) {
                $table->string('kode_eksternal', 32)->nullable()->after('slug');
            }
        });
    }
};
