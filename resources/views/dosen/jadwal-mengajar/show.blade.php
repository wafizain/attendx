@extends('layouts.master')

@section('title', 'Detail Jadwal Mengajar')
@section('page-title', 'Detail Jadwal Mengajar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('dosen.jadwal-mengajar.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chalkboard-teacher text-primary"></i> Detail Jadwal Mengajar
            </h1>
            <p class="text-muted mb-0">{{ $jadwal->mataKuliah->nama_mk }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Info Jadwal -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Jadwal</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150"><strong>Mata Kuliah:</strong></td>
                                    <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kode MK:</strong></td>
                                    <td>{{ $jadwal->mataKuliah->kode_mk }}</td>
                                </tr>
                                <tr>
                                    <td><strong>SKS:</strong></td>
                                    <td>{{ $jadwal->mataKuliah->sks }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td>{{ $jadwal->kelas ? $jadwal->kelas->nama : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150"><strong>Hari:</strong></td>
                                    <td>{{ $jadwal->hari_nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu:</strong></td>
                                    <td>{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ruangan:</strong></td>
                                    <td>{{ $jadwal->ruangan->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Peserta:</strong></td>
                                    <td>{{ $jadwal->mahasiswa->count() }} Mahasiswa</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pertemuan Terbaru -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Pertemuan Terbaru</h6>
                </div>
                <div class="card-body">
                    @if($pertemuanTerbaru->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Minggu</th>
                                    <th>Tanggal</th>
                                    <th>Materi</th>
                                    <th>Status</th>
                                    <th>Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pertemuanTerbaru as $pertemuan)
                                <tr>
                                    <td>{{ $pertemuan->minggu_ke }}</td>
                                    <td>{{ $pertemuan->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $pertemuan->materi ?? '-' }}</td>
                                    <td>
                                        @if($pertemuan->status_sesi == 'direncanakan')
                                            <span class="badge bg-secondary">Direncanakan</span>
                                        @elseif($pertemuan->status_sesi == 'berjalan')
                                            <span class="badge bg-primary">Berjalan</span>
                                        @elseif($pertemuan->status_sesi == 'selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @else
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pertemuan->status_sesi == 'selesai')
                                            @php
                                                $stats = $pertemuan->getStatistikKehadiran();
                                            @endphp
                                            <small>
                                                <span class="text-success">{{ $stats['hadir'] + $stats['telat'] }}</span>/{{ $stats['total_peserta'] }}
                                                ({{ $stats['persentase_hadir'] }}%)
                                            </small>
                                        @else
                                            -
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
                        <p class="text-muted">Belum ada pertemuan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Statistik -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Statistik Kehadiran</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Pertemuan</span>
                            <strong>{{ $statistik['total_pertemuan'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Mahasiswa</span>
                            <strong>{{ $statistik['total_mahasiswa'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Rata-rata Kehadiran</span>
                            <strong>{{ $statistik['rata_kehadiran'] }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $statistik['rata_kehadiran'] }}%">
                                {{ $statistik['rata_kehadiran'] }}%
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ $statistik['hadir'] }}</h4>
                                <small class="text-muted">Hadir</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $statistik['izin'] }}</h4>
                                <small class="text-muted">Izin</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-notes-medical fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ $statistik['sakit'] }}</h4>
                                <small class="text-muted">Sakit</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0">{{ $statistik['alpha'] }}</h4>
                                <small class="text-muted">Alpha</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('dosen.jadwal-mengajar.index') }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Jadwal
                    </a>
                    <a href="{{ route('reports.by-class') }}" class="btn btn-info w-100 mb-2">
                        <i class="fas fa-file-lines"></i> Lihat Laporan
                    </a>
                    <a href="{{ route('dosen.absen-manual') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-edit"></i> Absen Manual
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
