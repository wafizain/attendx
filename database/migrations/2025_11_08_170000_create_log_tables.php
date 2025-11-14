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
        // Log password views
        if (!Schema::hasTable('log_password_views')) {
            Schema::create('log_password_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id');
                $table->unsignedBigInteger('dosen_id');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('dosen_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('created_at');
            });
        }

        // Log device access
        if (!Schema::hasTable('log_device_access')) {
            Schema::create('log_device_access', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_id')->nullable();
                $table->string('action', 50);
                $table->string('endpoint', 200);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->integer('response_code')->nullable();
                $table->string('response_message', 500)->nullable();
                $table->json('request_data')->nullable();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
                $table->index('created_at');
                $table->index('action');
            });
        }

        // Update devices table untuk pairing
        if (Schema::hasTable('devices')) {
            if (!Schema::hasColumn('devices', 'api_key')) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->string('api_key', 255)->nullable()->after('status');
                    $table->timestamp('activated_at')->nullable()->after('last_sync');
                    $table->unsignedBigInteger('activated_by')->nullable()->after('activated_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_device_access');
        Schema::dropIfExists('log_password_views');

        if (Schema::hasTable('devices')) {
            if (Schema::hasColumn('devices', 'api_key')) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->dropColumn(['api_key', 'activated_at', 'activated_by']);
                });
            }
        }
    }
};
