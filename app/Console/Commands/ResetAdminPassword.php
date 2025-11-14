<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password admin berdasarkan email (untuk emergency)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Cari user berdasarkan email
        $user = User::where('email', $email)
                    ->where('role', 'admin')
                    ->first();

        if (!$user) {
            $this->error("Admin dengan email '{$email}' tidak ditemukan!");
            return 1;
        }

        // Konfirmasi
        if (!$this->confirm("Reset password untuk admin '{$user->name}' ({$user->email})?")) {
            $this->info('Reset password dibatalkan.');
            return 0;
        }

        // Generate password random
        $newPassword = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
            'first_login' => true
        ]);

        // Tampilkan hasil
        $this->newLine();
        $this->info('✓ Password berhasil direset!');
        $this->newLine();
        
        $this->table(
            ['Field', 'Value'],
            [
                ['Nama', $user->name],
                ['Email', $user->email],
                ['Password Baru', $newPassword],
            ]
        );

        $this->newLine();
        $this->warn('PENTING:');
        $this->warn('- Salin password di atas dan berikan kepada admin yang bersangkutan');
        $this->warn('- Admin harus mengganti password saat login pertama');
        $this->warn('- Password ini tidak akan ditampilkan lagi');
        $this->newLine();

        return 0;
    }
}
