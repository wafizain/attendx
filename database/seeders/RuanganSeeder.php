<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruangan;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        $ruanganList = [
            ['kode' => 'G001', 'nama' => 'Gedung A - 101', 'kapasitas' => 40, 'lokasi' => 'Gedung A Lantai 1', 'status' => 'aktif'],
            ['kode' => 'G002', 'nama' => 'Gedung A - 102', 'kapasitas' => 40, 'lokasi' => 'Gedung A Lantai 1', 'status' => 'aktif'],
            ['kode' => 'LAB1', 'nama' => 'Laboratorium Komputer 1', 'kapasitas' => 30, 'lokasi' => 'Gedung Lab Lantai 2', 'status' => 'aktif'],
            ['kode' => 'LAB2', 'nama' => 'Laboratorium Komputer 2', 'kapasitas' => 30, 'lokasi' => 'Gedung Lab Lantai 2', 'status' => 'aktif'],
            ['kode' => 'AULA', 'nama' => 'Aula', 'kapasitas' => 200, 'lokasi' => 'Gedung Pusat', 'status' => 'aktif'],
        ];

        foreach ($ruanganList as $r) {
            Ruangan::updateOrCreate(
                ['kode' => $r['kode']],
                [
                    'nama' => $r['nama'],
                    'kapasitas' => $r['kapasitas'],
                    'lokasi' => $r['lokasi'],
                    'status' => $r['status'],
                    'keterangan' => null,
                ]
            );
        }
    }
}
