<?php

namespace App\Exports;

use App\Models\KelasMember;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KelasMemberExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $members;

    public function __construct($members)
    {
        $this->members = $members;
    }

    public function collection()
    {
        return $this->members;
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Tanggal Masuk',
            'Tanggal Keluar',
            'Status',
            'Durasi (Hari)',
            'Keterangan',
        ];
    }

    public function map($member): array
    {
        return [
            $member->nim,
            $member->mahasiswa ? $member->mahasiswa->name : '-',
            $member->tanggal_masuk->format('Y-m-d'),
            $member->tanggal_keluar ? $member->tanggal_keluar->format('Y-m-d') : '-',
            $member->status_label,
            $member->durasi,
            $member->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
