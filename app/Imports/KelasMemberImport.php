<?php

namespace App\Imports;

use App\Models\KelasMember;
use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class KelasMemberImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithEvents
{
    use SkipsErrors;

    protected $kelasId;
    protected $importedNims = [];

    public function __construct($kelasId)
    {
        $this->kelasId = $kelasId;
    }

    public function model(array $row)
    {
        // Check if already active member
        $existing = KelasMember::where('id_kelas', $this->kelasId)
            ->where('nim', $row['nim'])
            ->aktif()
            ->first();
        
        if ($existing) {
            // Skip if already active
            return null;
        }

        // Collect NIMs for batch update
        $this->importedNims[] = $row['nim'];

        // Create new member
        return new KelasMember([
            'id_kelas' => $this->kelasId,
            'nim' => $row['nim'],
            'tanggal_masuk' => $row['tanggal_masuk'],
            'keterangan' => $row['keterangan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nim' => 'required|exists:mahasiswa,nim',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:150',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                // Update all imported mahasiswa's id_kelas
                if (!empty($this->importedNims)) {
                    Mahasiswa::whereIn('nim', $this->importedNims)
                        ->update(['id_kelas' => $this->kelasId]);
                }
            },
        ];
    }
}
