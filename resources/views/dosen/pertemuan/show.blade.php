@extends('layouts.master')

@section('title', 'Detail Pertemuan - Dosen')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Detail Pertemuan
                    </h3>
                    <a href="{{ route('pertemuan.index') }}" class="btn btn-secondary float-end">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <!-- Informasi Pertemuan -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Informasi Pertemuan</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Mata Kuliah:</strong></td>
                                    <td>{{ $pertemuan->jadwal->mataKuliah->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td>{{ $pertemuan->jadwal->kelas->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pertemuan Ke:</strong></td>
                                    <td>{{ $pertemuan->pertemuan_ke }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td>{{ $pertemuan->tanggal->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu:</strong></td>
                                    <td>{{ $pertemuan->jam_mulai }} - {{ $pertemuan->jam_selesai }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ruangan:</strong></td>
                                    <td>{{ $pertemuan->ruangan->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @switch($pertemuan->status)
                                            @case('scheduled')
                                                <span class="badge bg-secondary">Terjadwal</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-success">Aktif</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-primary">Selesai</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Dibatalkan</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $pertemuan->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Statistik Kehadiran</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $totalMahasiswa }}</h4>
                                            <small>Total Mahasiswa</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $hadirCount }}</h4>
                                            <small>Hadir</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-4">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $izinCount }}</h4>
                                            <small>Izin</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sakitCount }}</h4>
                                            <small>Sakit</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $alpaCount }}</h4>
                                            <small>Alpa</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Pertemuan -->
                    @if($pertemuan->catatan)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Catatan Pertemuan</h5>
                                <div class="alert alert-info">
                                    {{ $pertemuan->catatan }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Daftar Kehadiran -->
                    <div class="row">
                        <div class="col-12">
                            <h5>Daftar Kehadiran Mahasiswa</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Status Kehadiran</th>
                                            <th>Waktu Absen</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pertemuan->absensi as $index => $absensi)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $absensi->mahasiswa->nim ?? '-' }}</td>
                                                <td>{{ $absensi->mahasiswa->nama ?? '-' }}</td>
                                                <td>
                                                    @switch($absensi->status)
                                                        @case('hadir')
                                                            <span class="badge bg-success">Hadir</span>
                                                            @break
                                                        @case('izin')
                                                            <span class="badge bg-warning">Izin</span>
                                                            @break
                                                        @case('sakit')
                                                            <span class="badge bg-info">Sakit</span>
                                                            @break
                                                        @case('alpa')
                                                            <span class="badge bg-danger">Alpa</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $absensi->status }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ $absensi->waktu_absen ? $absensi->waktu_absen->format('H:i:s') : '-' }}</td>
                                                <td>{{ $absensi->keterangan ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data kehadiran</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
