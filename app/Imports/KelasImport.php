<?php

namespace App\Imports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class KelasImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        // Prevent duplication by (prodi_id + angkatan + kode)
        $existing = Kelas::where('prodi_id', $row['prodi_id'])
            ->where('angkatan', $row['angkatan'])
            ->where('kode', $row['kode'])
            ->first();
        
        if ($existing) {
            // Update existing
            $existing->update([
                'nama' => $row['nama'],
                'kapasitas' => $row['kapasitas'] ?? null,
                'wali_dosen_id' => $row['wali_dosen_id'] ?? null,
                'status' => $row['status'] ?? 1,
            ]);
            
            return null;
        }

        // Create new
        return new Kelas([
            'kode' => strtoupper($row['kode']),
            'nama' => $row['nama'],
            'prodi_id' => $row['prodi_id'],
            'angkatan' => $row['angkatan'],
            'kapasitas' => $row['kapasitas'] ?? null,
            'wali_dosen_id' => $row['wali_dosen_id'] ?? null,
            'status' => $row['status'] ?? 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:24|regex:/^[A-Z0-9\-_.]+$/',
            'nama' => 'required|string|min:3|max:100',
            'prodi_id' => 'required|exists:program_studi,id',
            'angkatan' => 'required|integer|min:2000',
            'kapasitas' => 'nullable|integer|min:1',
            'wali_dosen_id' => 'nullable|exists:users,id',
            'status' => 'nullable|boolean',
        ];
    }
}
