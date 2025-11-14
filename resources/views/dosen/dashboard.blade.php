@extends('layouts.master')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Dashboard Dosen</h2>
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
                            <h6 class="text-muted mb-1">Total Kelas</h6>
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
                                <i class="fas fa-calendar-check text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Sesi Aktif</h6>
                            <h3 class="mb-0">{{ $sesiAktif }}</h3>
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
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-calendar-day text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Jadwal Hari Ini</h6>
                            <h3 class="mb-0">{{ $jadwalHariIni->count() }}</h3>
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
                                <i class="fas fa-play-circle text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Kelas Berlangsung</h6>
                            <h3 class="mb-0">{{ $kelasBerlangsung->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-3">
        <!-- Jadwal Hari Ini -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-day text-primary me-2"></i>
                        Jadwal Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($jadwalHariIni as $jadwal)
                    <div class="d-flex align-items-center mb-3 p-2 rounded border-start border-4 border-primary bg-light">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">{{ $jadwal->kelas->nama_kelas }}</h6>
                            <p class="mb-1 text-muted small">{{ $jadwal->kelas->mataKuliah->nama_matkul }}</p>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                {{ $jadwal->waktu_mulai }} - {{ $jadwal->waktu_selesai }}
                            </div>
                            @if($jadwal->topik)
                            <div class="text-muted small mt-1">
                                <i class="fas fa-book me-1"></i>
                                {{ $jadwal->topik }}
                            </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            @if($jadwal->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @elseif($jadwal->status == 'selesai')
                                <span class="badge bg-secondary">Selesai</span>
                            @else
                                <span class="badge bg-warning">Draft</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>Tidak ada jadwal hari ini</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kelas Sedang Berlangsung -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-play-circle text-warning me-2"></i>
                        Kelas Sedang Berlangsung
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($kelasBerlangsung as $kelas)
                    <div class="d-flex align-items-center mb-3 p-2 rounded border-start border-4 border-warning bg-light">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">{{ $kelas->kelas->nama_kelas }}</h6>
                            <p class="mb-1 text-muted small">{{ $kelas->kelas->mataKuliah->nama_matkul }}</p>
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                Mulai: {{ $kelas->started_at ? $kelas->started_at->format('H:i') : '-' }}
                            </div>
                            @if($kelas->kode_absensi)
                            <div class="text-primary small mt-1">
                                <i class="fas fa-key me-1"></i>
                                Kode: {{ $kelas->kode_absensi }}
                            </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('dosen.jadwal-mengajar.sesi', $kelas->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-tachometer-alt"></i> Kelola
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-stop-circle fa-3x mb-3"></i>
                        <p>Tidak ada kelas yang berlangsung</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Statistik Kehadiran Hari Ini -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie text-info me-2"></i>
                        Statistik Kehadiran Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-success bg-opacity-10">
                                <h4 class="mb-0 text-success">{{ $statistikKehadiran['hadir'] }}</h4>
                                <small class="text-success">Hadir</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-warning bg-opacity-10">
                                <h4 class="mb-0 text-warning">{{ $statistikKehadiran['izin'] }}</h4>
                                <small class="text-warning">Izin</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-info bg-opacity-10">
                                <h4 class="mb-0 text-info">{{ $statistikKehadiran['sakit'] }}</h4>
                                <small class="text-info">Sakit</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-danger bg-opacity-10">
                                <h4 class="mb-0 text-danger">{{ $statistikKehadiran['alpha'] }}</h4>
                                <small class="text-danger">Alpha</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifikasi Fingerprint & Riwayat Pertemuan -->
    <div class="row g-3 mt-1">
        <!-- Notifikasi Absensi Fingerprint -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-fingerprint text-primary me-2"></i>
                            Notifikasi Absensi Fingerprint
                        </h5>
                        <small class="text-muted">Hari ini</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Mahasiswa</th>
                                    <th>Mata Kuliah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifikasiFingerprint as $notif)
                                <tr>
                                    <td>
                                        <small>{{ $notif->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-1 me-2">
                                                <i class="fas fa-user text-primary" style="font-size: 10px;"></i>
                                            </div>
                                            <small>{{ $notif->mahasiswa?->name ?? 'Tidak diketahui' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $notif->sesiAbsensi->kelas->mataKuliah->nama_matkul }}</small>
                                    </td>
                                    <td>
                                        @if($notif->status == 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($notif->status == 'izin')
                                            <span class="badge bg-warning">Izin</span>
                                        @elseif($notif->status == 'sakit')
                                            <span class="badge bg-info">Sakit</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada notifikasi fingerprint hari ini</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pertemuan Terakhir -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-secondary me-2"></i>
                        Riwayat Pertemuan Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($riwayatPertemuan as $riwayat)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-secondary bg-opacity-10 p-2">
                                    <i class="fas fa-calendar-alt text-secondary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $riwayat->kelas->nama_kelas }}</h6>
                                <p class="mb-1 text-muted small">{{ $riwayat->kelas->mataKuliah->nama_matkul }}</p>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $riwayat->tanggal->format('d M Y') }}
                                    @if($riwayat->pertemuan_ke)
                                    <span class="ms-2">
                                        <i class="fas fa-list-ol me-1"></i>
                                        Pertemuan {{ $riwayat->pertemuan_ke }}
                                    </span>
                                    @endif
                                </div>
                                @if($riwayat->topik)
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-book me-1"></i>
                                    {{ $riwayat->topik }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <p>Belum ada riwayat pertemuan</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    max-height: 300px;
    overflow-y: auto;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.border-start {
    border-left-width: 4px !important;
}

.badge {
    font-size: 0.75rem;
}
</style>

@endsection
