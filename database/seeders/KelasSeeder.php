<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Prodi;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure at least one Prodi exists
        $prodi = Prodi::firstOrCreate(
            ['kode' => 'IF'],
            [
                'nama' => 'Informatika',
                'jenjang' => 'S1',
                'status' => 1,
            ]
        );

        $data = [
            [
                'kode' => 'IF-A',
                'nama' => 'Informatika A',
                'angkatan' => (int) date('Y') - 2,
                'semester_aktif' => 5,
                'kapasitas' => 40,
                'status' => 1,
                'catatan' => null,
            ],
            [
                'kode' => 'IF-B',
                'nama' => 'Informatika B',
                'angkatan' => (int) date('Y') - 1,
                'semester_aktif' => 3,
                'kapasitas' => 40,
                'status' => 1,
                'catatan' => null,
            ],
            [
                'kode' => 'IF-C',
                'nama' => 'Informatika C',
                'angkatan' => (int) date('Y'),
                'semester_aktif' => 1,
                'kapasitas' => 40,
                'status' => 1,
                'catatan' => null,
            ],
        ];

        foreach ($data as $row) {
            Kelas::updateOrCreate(
                [
                    'prodi_id' => $prodi->id,
                    'angkatan' => $row['angkatan'],
                    'kode' => $row['kode'],
                ],
                [
                    'nama' => $row['nama'],
                    'semester_aktif' => $row['semester_aktif'],
                    'kapasitas' => $row['kapasitas'],
                    'status' => $row['status'],
                    'catatan' => $row['catatan'],
                ]
            );
        }
    }
}
