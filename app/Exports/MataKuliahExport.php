<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MataKuliahExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $mataKuliahList;

    public function __construct($mataKuliahList = null)
    {
        $this->mataKuliahList = $mataKuliahList;
    }

    public function collection()
    {
        if ($this->mataKuliahList) {
            return $this->mataKuliahList;
        }

        return MataKuliah::with(['prodi', 'pengampu'])->get();
    }

    public function headings(): array
    {
        return [
            'Kode MK',
            'Nama MK',
            'Prodi',
            'Kurikulum',
            'SKS',
            'Jenis',
            'Semester Rekomendasi',
            'Total Kelas',
            'Total Pengampu',
            'Status',
        ];
    }

    public function map($mk): array
    {
        return [
            $mk->kode_mk,
            $mk->nama_mk,
            $mk->prodi ? $mk->prodi->nama : '-',
            $mk->kurikulum,
            $mk->sks,
            $mk->jenis,
            $mk->semester_rekomendasi ?? '-',
            $mk->kelas()->count(),
            $mk->pengampu()->count(),
            $mk->status ? 'Aktif' : 'Nonaktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
