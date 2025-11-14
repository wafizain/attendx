<?php

namespace App\Imports;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ProdiImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Prevent duplication by kode
        $existing = Prodi::where('kode', $row['kode'])->first();
        
        if ($existing) {
            // Update existing
            $existing->update([
                'nama' => $row['nama'],
                'jenjang' => $row['jenjang'],
                'akreditasi' => $row['akreditasi'] ?? null,
                'email_kontak' => $row['email_kontak'] ?? null,
                'telepon_kontak' => $row['telepon_kontak'] ?? null,
                'kode_eksternal' => $row['kode_eksternal'] ?? null,
                'status' => $row['status'] ?? 1,
                'slug' => Str::slug($row['nama']),
            ]);
            
            return null;
        }

        // Create new
        return new Prodi([
            'kode' => $row['kode'],
            'nama' => $row['nama'],
            'jenjang' => $row['jenjang'],
            'akreditasi' => $row['akreditasi'] ?? null,
            'email_kontak' => $row['email_kontak'] ?? null,
            'telepon_kontak' => $row['telepon_kontak'] ?? null,
            'kode_eksternal' => $row['kode_eksternal'] ?? null,
            'status' => $row['status'] ?? 1,
            'slug' => Str::slug($row['nama']),
        ]);
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:16',
            'nama' => 'required|string|max:150',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3',
            'akreditasi' => 'nullable|in:A,B,C,Baik,Baik Sekali,Unggul',
            'email_kontak' => 'nullable|email|max:150',
            'telepon_kontak' => 'nullable|string|max:32',
            'kode_eksternal' => 'nullable|string|max:32',
            'status' => 'nullable|boolean',
        ];
    }
}
