<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $dosenList = [
            [
                'name' => 'Dr. Andi Pratama',
                'username' => 'dosen.andi',
                'no_induk' => 'DSN101',
                'email' => 'dosen.andi@example.com',
            ],
            [
                'name' => 'Dr. Budi Santoso',
                'username' => 'dosen.budi',
                'no_induk' => 'DSN102',
                'email' => 'dosen.budi@example.com',
            ],
            [
                'name' => 'Ir. Citra Dewi, M.Kom',
                'username' => 'dosen.citra',
                'no_induk' => 'DSN103',
                'email' => 'dosen.citra@example.com',
            ],
        ];

        foreach ($dosenList as $d) {
            User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
                    'username' => $d['username'],
                    'no_induk' => $d['no_induk'],
                    'password' => Hash::make('12345678'),
                    'role' => 'dosen',
                    'status' => 'aktif',
                    'first_login' => false,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
