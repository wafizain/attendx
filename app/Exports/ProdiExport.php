<?php

namespace App\Exports;

use App\Models\Prodi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProdiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $prodis;

    public function __construct($prodis = null)
    {
        $this->prodis = $prodis;
    }

    public function collection()
    {
        if ($this->prodis) {
            return $this->prodis;
        }

        return Prodi::with('kaprodi')->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Program Studi',
            'Jenjang',
            'Akreditasi',
            'Kaprodi',
            'Deskripsi',
            'Email Kontak',
            'Telepon Kontak',
            'Kode Eksternal',
            'Status',
            'Total Mahasiswa',
            'Total Kelas',
            'Total Mata Kuliah',
        ];
    }

    public function map($prodi): array
    {
        return [
            $prodi->kode,
            $prodi->nama,
            $prodi->jenjang,
            $prodi->akreditasi ?? '-',
            $prodi->kaprodi ? $prodi->kaprodi->name : '-',
            $prodi->deskripsi ?? '-',
            $prodi->email_kontak ?? '-',
            $prodi->telepon_kontak ?? '-',
            $prodi->kode_eksternal ?? '-',
            $prodi->status ? 'Aktif' : 'Nonaktif',
            $prodi->mahasiswa()->count(),
            $prodi->kelas()->count(),
            $prodi->mataKuliah()->count(),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
