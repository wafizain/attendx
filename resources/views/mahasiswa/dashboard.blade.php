@extends('layouts.master')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Dashboard Mahasiswa</h2>
            <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-door-open text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Kelas Diikuti</h6>
                            <h3 class="mb-0">{{ $totalKelas }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Hadir</h6>
                            <h3 class="mb-0">{{ $hadirCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-exclamation-circle text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Izin/Sakit</h6>
                            <h3 class="mb-0">{{ $izinCount + $sakitCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="fas fa-times-circle text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Alpha</h6>
                            <h3 class="mb-0">{{ $alphaCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Chart & Class List -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Persentase Kehadiran
                    </h5>
                    <div class="text-center py-4">
                        <div class="position-relative d-inline-block">
                            <h1 class="display-3 fw-bold text-primary">{{ $persentaseKehadiran }}%</h1>
                        </div>
                        <p class="text-muted mt-3">Dari {{ $totalAbsensi }} total pertemuan</p>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseKehadiran }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-book-open text-primary me-2"></i>
                        Kelas yang Diikuti
                    </h5>
                    <div class="row g-3">
                        @forelse($kelasList as $kelas)
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $kelas->nama_kelas }}</h6>
                                    <p class="card-text text-muted mb-2">{{ $kelas->mataKuliah->nama_matkul }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $kelas->dosen->name }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum terdaftar di kelas manapun</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-history text-primary me-2"></i>
                        Riwayat Absensi Terbaru
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Mata Kuliah</th>
                                    <th>Kelas</th>
                                    <th>Pertemuan</th>
                                    <th>Status</th>
                                    <th>Waktu Absen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAbsensi as $absensi)
                                <tr>
                                    <td>{{ $absensi->sesiAbsensi->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $absensi->sesiAbsensi->kelas->mataKuliah->nama_matkul }}</td>
                                    <td>{{ $absensi->sesiAbsensi->kelas->nama_kelas }}</td>
                                    <td>Pertemuan {{ $absensi->sesiAbsensi->pertemuan_ke ?? '-' }}</td>
                                    <td>
                                        @if($absensi->status == 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($absensi->status == 'izin')
                                            <span class="badge bg-info">Izin</span>
                                        @elseif($absensi->status == 'sakit')
                                            <span class="badge bg-warning">Sakit</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($absensi->waktu_absen)
                                            {{ \Carbon\Carbon::parse($absensi->waktu_absen)->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada riwayat absensi</td>
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
@endsection
