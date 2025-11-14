<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        $semesters = [
            [
                'tahun_ajaran' => '2024/2025',
                'semester' => 1,
                'status' => 'aktif',
                'tanggal_mulai' => Carbon::create(2024, 9, 1),
                'tanggal_selesai' => Carbon::create(2025, 1, 31),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tahun_ajaran' => '2023/2024',
                'semester' => 2,
                'status' => 'tidak_aktif',
                'tanggal_mulai' => Carbon::create(2024, 2, 1),
                'tanggal_selesai' => Carbon::create(2024, 6, 30),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tahun_ajaran' => '2023/2024',
                'semester' => 1,
                'status' => 'tidak_aktif',
                'tanggal_mulai' => Carbon::create(2023, 9, 1),
                'tanggal_selesai' => Carbon::create(2024, 1, 31),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('semesters')->insert($semesters);
    }
}
