<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus user lama jika ada
        User::whereIn('email', ['admin@gmail.com', 'dosen@gmail.com', 'mahasiswa@gmail.com'])->delete();

        // Buat Admin
        User::create([
            'name' => 'Admin Utama',
            'no_induk' => 'ADM001',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'status' => 'aktif',
            'first_login' => false,
        ]);

        // Buat Dosen
        User::create([
            'name' => 'Dr. Budi Santoso',
            'no_induk' => 'DSN001',
            'email' => 'dosen@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'dosen',
            'status' => 'aktif',
            'first_login' => false,
        ]);

        // Buat Mahasiswa
        User::create([
            'name' => 'Andi Wijaya',
            'no_induk' => '2024001',
            'email' => 'mahasiswa@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'mahasiswa',
            'status' => 'aktif',
            'first_login' => false,
        ]);

        echo "✅ User berhasil dibuat!\n";
        echo "Admin: admin@gmail.com / 12345678\n";
        echo "Dosen: dosen@gmail.com / 12345678\n";
        echo "Mahasiswa: mahasiswa@gmail.com / 12345678\n";
    }
}
