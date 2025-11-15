@extends('layouts.master')

@section('title', 'Detail Jadwal')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('mahasiswa.jadwal') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fas fa-arrow-left me-1"></i>
                Kembali ke Jadwal
            </a>
            <h2 class="fw-bold">Detail Jadwal Kuliah</h2>
        </div>
    </div>

    <!-- Informasi Jadwal -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>
                        {{ $jadwal->mataKuliah->nama_mk ?? 'Mata Kuliah' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Kode Mata Kuliah:</strong><br>
                                {{ $jadwal->mataKuliah->kode_mk ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>SKS:</strong><br>
                                {{ $jadwal->mataKuliah->sks ?? 0 }} SKS
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Dosen Pengampu:</strong><br>
                                {{ $jadwal->dosen->name ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Kelas:</strong><br>
                                {{ $jadwal->kelas->nama ?? $jadwal->kelas->nama_kelas ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Hari:</strong><br>
                                @php
                                    $hariMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                @endphp
                                {{ $hariMap[$jadwal->hari] ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Waktu:</strong><br>
                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Ruangan:</strong><br>
                                {{ $jadwal->ruangan->nama ?? 'Belum ditentukan' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Periode:</strong><br>
                                {{ optional($jadwal->tanggal_mulai)->format('d/m/Y') ?? '-' }} s/d {{ optional($jadwal->tanggal_selesai)->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Kehadiran -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Statistik Kehadiran Anda
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="fw-bold text-success">{{ $statistik['persentase_kehadiran'] }}%</h2>
                        <p class="text-muted small mb-0">Persentase Kehadiran</p>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Pertemuan:</span>
                        <strong>{{ $statistik['total_pertemuan'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-check-circle text-success me-1"></i> Hadir:</span>
                        <strong>{{ $statistik['hadir'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-info-circle text-info me-1"></i> Izin:</span>
                        <strong>{{ $statistik['izin'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-hospital text-warning me-1"></i> Sakit:</span>
                        <strong>{{ $statistik['sakit'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-times-circle text-danger me-1"></i> Alpha:</span>
                        <strong>{{ $statistik['alpha'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Pertemuan -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Riwayat Pertemuan Terakhir
                    </h6>
                </div>
                <div class="card-body">
                    @if($pertemuanList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Pertemuan</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pertemuanList as $pertemuan)
                                <tr>
                                    <td>Pertemuan {{ $pertemuan->minggu_ke }}</td>
                                    <td>{{ optional($pertemuan->tanggal)->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ substr($pertemuan->jam_mulai, 0, 5) }} - {{ substr($pertemuan->jam_selesai, 0, 5) }}</td>
                                    <td>
                                        @if($pertemuan->status_sesi == 'selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($pertemuan->status_sesi == 'berjalan')
                                            <span class="badge bg-primary">Berjalan</span>
                                        @elseif($pertemuan->status_sesi == 'dibatalkan')
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        @else
                                            <span class="badge bg-secondary">Direncanakan</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada pertemuan yang dilaksanakan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
