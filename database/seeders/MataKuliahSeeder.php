<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataKuliah;
use App\Models\Prodi;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a Prodi exists (same as used by KelasSeeder)
        $prodi = Prodi::firstOrCreate(
            ['kode' => 'IF'],
            [
                'nama' => 'Informatika',
                'jenjang' => 'S1',
                'status' => 1,
            ]
        );

        $mkList = [
            [
                'kode_mk' => 'IF101',
                'nama_mk' => 'Algoritma dan Pemrograman',
                'sks' => 3,
                'jenis' => 'Teori',
                'semester' => 1,
                'status' => 1,
                'kurikulum' => date('Y'),
            ],
            [
                'kode_mk' => 'IF102',
                'nama_mk' => 'Struktur Data',
                'sks' => 3,
                'jenis' => 'Teori',
                'semester' => 2,
                'status' => 1,
                'kurikulum' => date('Y'),
            ],
            [
                'kode_mk' => 'IF201',
                'nama_mk' => 'Basis Data',
                'sks' => 3,
                'jenis' => 'Teori',
                'semester' => 3,
                'status' => 1,
                'kurikulum' => date('Y'),
            ],
            [
                'kode_mk' => 'IF202',
                'nama_mk' => 'Jaringan Komputer',
                'sks' => 3,
                'jenis' => 'Teori',
                'semester' => 3,
                'status' => 1,
                'kurikulum' => date('Y'),
            ],
        ];

        foreach ($mkList as $mk) {
            MataKuliah::updateOrCreate(
                ['kode_mk' => $mk['kode_mk']],
                [
                    'id_prodi' => $prodi->id,
                    'nama_mk' => $mk['nama_mk'],
                    'sks' => $mk['sks'],
                    'jenis' => $mk['jenis'],
                    'semester' => $mk['semester'],
                    'status' => $mk['status'],
                    'kurikulum' => $mk['kurikulum'],
                ]
            );
        }
    }
}
