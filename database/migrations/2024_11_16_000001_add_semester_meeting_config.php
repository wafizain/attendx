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
        // Add columns to semesters table for meeting configuration
        Schema::table('semesters', function (Blueprint $table) {
            $table->integer('jumlah_pertemuan')->default(16)->after('status')->comment('Total pertemuan dalam semester');
            $table->integer('pertemuan_uts')->nullable()->after('jumlah_pertemuan')->comment('Pertemuan ke berapa UTS');
            $table->integer('pertemuan_uas')->nullable()->after('pertemuan_uts')->comment('Pertemuan ke berapa UAS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['jumlah_pertemuan', 'pertemuan_uts', 'pertemuan_uas']);
        });
    }
};
