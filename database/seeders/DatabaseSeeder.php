<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'no_induk' => 'ADM001',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 'aktif',
                'first_login' => false,
                'email_verified_at' => now(),
            ]
        );

        // Create Dosen User
        User::updateOrCreate(
            ['email' => 'dosen@gmail.com'],
            [
                'name' => 'Dosen',
                'username' => 'dosen',
                'no_induk' => 'DSN001',
                'email' => 'dosen@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'dosen',
                'status' => 'aktif',
                'first_login' => false,
                'email_verified_at' => now(),
            ]
        );

        // Create Mahasiswa User
        User::updateOrCreate(
            ['email' => 'mahasiswa@gmail.com'],
            [
                'name' => 'Mahasiswa',
                'username' => 'mahasiswa',
                'no_induk' => 'MHS001',
                'email' => 'mahasiswa@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'mahasiswa',
                'status' => 'aktif',
                'first_login' => false,
                'email_verified_at' => now(),
            ]
        );

        // Seed Kelas, Mata Kuliah, Dosen, Mahasiswa, dan Ruangan
        $this->call([
            KelasSeeder::class,
            MataKuliahSeeder::class,
            DosenSeeder::class,
            MahasiswaSeeder::class,
            RuanganSeeder::class,
        ]);
    }
}
