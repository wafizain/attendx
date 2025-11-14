<?php

namespace App\Imports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class MataKuliahImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        // Prevent duplication by (id_prodi + kurikulum + kode_mk)
        $existing = MataKuliah::where('id_prodi', $row['id_prodi'])
            ->where('kurikulum', $row['kurikulum'])
            ->where('kode_mk', $row['kode_mk'])
            ->first();
        
        if ($existing) {
            // Update existing
            $existing->update([
                'nama_mk' => $row['nama_mk'],
                'sks' => $row['sks'],
                'jenis' => $row['jenis'],
                'semester_rekomendasi' => $row['semester_rekomendasi'] ?? null,
                'status' => $row['status'] ?? 1,
            ]);
            
            return null;
        }

        // Create new
        return new MataKuliah([
            'id_prodi' => $row['id_prodi'],
            'kurikulum' => $row['kurikulum'],
            'kode_mk' => strtoupper($row['kode_mk']),
            'nama_mk' => $row['nama_mk'],
            'sks' => $row['sks'],
            'jenis' => $row['jenis'],
            'semester_rekomendasi' => $row['semester_rekomendasi'] ?? null,
            'status' => $row['status'] ?? 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'id_prodi' => 'required|exists:program_studi,id',
            'kurikulum' => 'required|regex:/^[A-Za-z0-9-_]{2,20}$/',
            'kode_mk' => 'required|regex:/^[A-Z0-9._-]{2,20}$/',
            'nama_mk' => 'required|min:3|max:150',
            'sks' => 'required|integer|min:1|max:6',
            'jenis' => 'required|in:Teori,Praktikum,Teori+Praktikum',
            'semester_rekomendasi' => 'nullable|integer|min:1|max:14',
            'status' => 'nullable|boolean',
        ];
    }
}
