<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Mahasiswa::with(['prodi', 'kelas', 'user']);

        // Apply filters
        if (!empty($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        }

        if (!empty($this->filters['prodi'])) {
            $query->where('id_prodi', $this->filters['prodi']);
        }

        if (!empty($this->filters['angkatan'])) {
            $query->where('angkatan', $this->filters['angkatan']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status_akademik', $this->filters['status']);
        }

        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        return $query->orderBy('nim');
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Email',
            'No HP',
            'Prodi',
            'Kelas',
            'Angkatan',
            'Status Akademik',
            'Biometrik',
            'Punya Akun',
            'Alamat',
        ];
    }

    public function map($mahasiswa): array
    {
        return [
            $mahasiswa->nim,
            $mahasiswa->nama,
            $mahasiswa->email ?? '-',
            $mahasiswa->no_hp ?? '-',
            $mahasiswa->prodi->nama ?? '-',
            $mahasiswa->kelas->kode ?? '-',
            $mahasiswa->angkatan,
            $mahasiswa->status_akademik_label,
            $mahasiswa->biometrik_status,
            $mahasiswa->hasUserAccount() ? 'Ya' : 'Tidak',
            $mahasiswa->alamat ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
