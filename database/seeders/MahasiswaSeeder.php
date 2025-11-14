<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::firstOrCreate(
            ['kode' => 'IF'],
            [
                'nama' => 'Informatika',
                'jenjang' => 'S1',
                'status' => 1,
            ]
        );

        // Ambil satu kelas untuk assign default
        $kelas = Kelas::where('prodi_id', $prodi->id)->orderBy('angkatan', 'desc')->first();

        $mahasiswaList = [
            ['nim' => '2310001', 'nama' => 'Ahmad Fauzi', 'email' => '2310001@example.com', 'angkatan' => (int) date('Y') - 2],
            ['nim' => '2310002', 'nama' => 'Bella Sari', 'email' => '2310002@example.com', 'angkatan' => (int) date('Y') - 2],
            ['nim' => '2410001', 'nama' => 'Cahyo Pratama', 'email' => '2410001@example.com', 'angkatan' => (int) date('Y') - 1],
            ['nim' => '2510001', 'nama' => 'Dewi Lestari', 'email' => '2510001@example.com', 'angkatan' => (int) date('Y')],
        ];

        foreach ($mahasiswaList as $m) {
            // Buat akun login user untuk mahasiswa jika belum ada
            $user = User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['nama'],
                    'username' => $m['nim'],
                    'no_induk' => $m['nim'],
                    'password' => Hash::make('12345678'),
                    'role' => 'mahasiswa',
                    'status' => 'aktif',
                    'first_login' => false,
                    'email_verified_at' => now(),
                ]
            );

            Mahasiswa::updateOrCreate(
                ['nim' => $m['nim']],
                [
                    'id_user' => $user->id,
                    'nama' => $m['nama'],
                    'email' => $m['email'],
                    'no_hp' => null,
                    'id_prodi' => $prodi->id,
                    'id_kelas' => $kelas->id ?? null,
                    'angkatan' => $m['angkatan'],
                    'status_akademik' => 'aktif',
                    'foto_path' => null,
                    'fp_enrolled' => false,
                    'face_enrolled' => false,
                    'alamat' => null,
                    'password_plain' => '12345678',
                ]
            );
        }
    }
}
