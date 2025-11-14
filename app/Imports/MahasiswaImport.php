<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MahasiswaImport implements ToCollection, WithHeadingRow
{
    protected $rowCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip if NIM already exists
            if (Mahasiswa::where('nim', $row['nim'])->exists()) {
                continue;
            }

            $userId = null;

            // Create user account if requested
            if (isset($row['create_account']) && $row['create_account'] == 1) {
                $tempPassword = Str::random(12);
                
                $user = User::create([
                    'name' => $row['nama'],
                    'no_induk' => $row['nim'],
                    'email' => $row['email'] ?? $row['nim'] . '@student.ac.id',
                    'password' => Hash::make($tempPassword),
                    'password_temp' => Hash::make($tempPassword),
                    'password_temp_expires_at' => now()->addHours(24),
                    'must_change_password' => 1,
                    'role' => 'mahasiswa',
                    'status' => 'aktif',
                ]);

                $userId = $user->id;
            }

            // Create mahasiswa
            Mahasiswa::create([
                'id_user' => $userId,
                'nim' => $row['nim'],
                'nama' => $row['nama'],
                'email' => $row['email'] ?? null,
                'no_hp' => $row['no_hp'] ?? null,
                'id_prodi' => $row['id_prodi'],
                'angkatan' => $row['angkatan'],
                'status_akademik' => $row['status_akademik'] ?? 'aktif',
            ]);

            $this->rowCount++;
        }
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
}
