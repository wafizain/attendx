<?php

namespace App\Exports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KelasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $kelasList;

    public function __construct($kelasList = null)
    {
        $this->kelasList = $kelasList;
    }

    public function collection()
    {
        if ($this->kelasList) {
            return $this->kelasList;
        }

        return Kelas::with(['prodi'])->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Kelas',
            'Prodi',
            'Angkatan',
            'Kapasitas',
            'Jumlah Mahasiswa Aktif',
            'Status',
        ];
    }

    public function map($kelas): array
    {
        return [
            $kelas->kode,
            $kelas->nama,
            $kelas->prodi ? $kelas->prodi->nama : '-',
            $kelas->angkatan,
            $kelas->kapasitas ?? '-',
            $kelas->membersAktif()->count(),
            $kelas->status ? 'Aktif' : 'Nonaktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
