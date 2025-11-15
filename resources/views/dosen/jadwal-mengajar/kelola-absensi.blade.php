@extends('layouts.master')

@section('title', 'Halaman Pertemuan')
@section('page-title', 'Halaman Pertemuan')

@push('styles')
<style>
    .meeting-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .stats-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .table-modern {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .table-modern thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
    }
    
    .table-modern tbody tr {
        border: none;
        transition: background-color 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .table-modern tbody td {
        padding: 1rem;
        border-top: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .search-box {
        border-radius: 25px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .search-box:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .btn-modern {
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .modal-xl {
        max-width: 1200px;
    }
    
    .table-active {
        background-color: rgba(102, 126, 234, 0.1) !important;
    }
    
    .avatar-sm {
        width: 35px;
        height: 35px;
    }
    
    .status-radio {
        cursor: pointer;
        width: 20px;
        height: 20px;
    }
    
    .status-radio:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }
    
    .table-warning {
        background-color: rgba(255, 193, 7, 0.15) !important;
    }
    
    .modal-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Modern Header -->
    <div class="meeting-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <a href="{{ route('dosen.jadwal-mengajar.index') }}" class="btn btn-light btn-sm mb-3">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1 class="h2 mb-2 fw-bold">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Pertemuan Berlangsung
                </h1>
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="mb-1 opacity-90">
                            @if(isset($jadwal) && $jadwal && $jadwal->mataKuliah)
                                {{ $jadwal->mataKuliah->nama_mk }}
                            @elseif(isset($mataKuliah) && $mataKuliah)
                                {{ $mataKuliah->nama_mk }}
                            @else
                                Mata Kuliah Tidak Ditemukan
                            @endif
                        </h4>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-users me-2"></i>
                            @if(isset($jadwal) && $jadwal && $jadwal->kelas)
                                {{ $jadwal->kelas->nama ?? $jadwal->kelas->nama_kelas ?? 'Kelas Tidak Ditemukan' }}
                            @else
                                {{ $sesiAbsensi->kelas->nama_kelas ?? 'Kelas Tidak Ditemukan' }}
                            @endif
                            • Pertemuan ke-{{ $sesiAbsensi->pertemuan_ke ?? '-' }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex flex-column align-items-end">
                            <div class="mb-2">
                                @if($sesiAbsensi->status == 'aktif')
                                    <span class="badge bg-success status-badge">
                                        <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>
                                        Sesi Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary status-badge">
                                        <i class="fas fa-stop-circle me-1"></i>
                                        Sesi Selesai
                                    </span>
                                @endif
                            </div>
                            <small class="opacity-75">
                                <i class="fas fa-clock me-1"></i>
                                {{ $sesiAbsensi->waktu_mulai ? $sesiAbsensi->waktu_mulai->format('H:i') : '-' }} WIB
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @if($sesiAbsensi->status == 'aktif')
            <div class="ms-3">
                <form action="{{ route('dosen.jadwal-mengajar.tutup-sesi', $sesiAbsensi->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-modern" 
                            onclick="return confirm('Yakin ingin mengakhiri pertemuan?')">
                        <i class="fas fa-stop me-2"></i>Akhiri Pertemuan
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Info Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card info-card h-100">
                <div class="card-body text-center">
                    <div class="stats-icon bg-primary bg-opacity-10">
                        <i class="fas fa-book text-primary fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1">Mata Kuliah</h6>
                    <p class="fw-bold mb-0">
                        @if(isset($mataKuliah) && $mataKuliah)
                            {{ $mataKuliah->kode_mk ?? '-' }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card info-card h-100">
                <div class="card-body text-center">
                    <div class="stats-icon bg-info bg-opacity-10">
                        <i class="fas fa-users text-info fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1">Total Mahasiswa</h6>
                    <p class="fw-bold mb-0">{{ $totalMahasiswa ?? 0 }} orang</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card info-card h-100">
                <div class="card-body text-center">
                    <div class="stats-icon bg-warning bg-opacity-10">
                        <i class="fas fa-clock text-warning fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1">Waktu Mulai</h6>
                    <p class="fw-bold mb-0">{{ $sesiAbsensi->waktu_mulai ? $sesiAbsensi->waktu_mulai->format('H:i') : '-' }} WIB</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card info-card h-100">
                <div class="card-body text-center">
                    <div class="stats-icon bg-success bg-opacity-10">
                        <i class="fas fa-hourglass-end text-success fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1">Waktu Selesai</h6>
                    <p class="fw-bold mb-0">{{ $sesiAbsensi->waktu_selesai ? $sesiAbsensi->waktu_selesai->format('H:i') : '-' }} WIB</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Kehadiran (Live) -->
    <div class="card info-card mb-4">
        <div class="card-header border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-chart-bar text-success me-2"></i>
                    Statistik Kehadiran Real-time
                </h5>
                <span class="badge bg-success">Live</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stats-card bg-success bg-opacity-10 border-0">
                        <div class="card-body text-center p-3">
                            <div class="stats-icon bg-success text-white mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            @php
                                $totalHadir = ($stats['hadir_fingerprint'] ?? 0) + ($stats['hadir_manual'] ?? 0);
                            @endphp
                            <h4 class="text-success mb-1 fw-bold">{{ $totalHadir }}</h4>
                            <small class="text-muted fw-medium">Hadir</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stats-card bg-warning bg-opacity-10 border-0">
                        <div class="card-body text-center p-3">
                            <div class="stats-icon bg-warning text-white mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h4 class="text-warning mb-1 fw-bold">{{ $stats['izin'] }}</h4>
                            <small class="text-muted fw-medium">Izin</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stats-card bg-danger bg-opacity-10 border-0">
                        <div class="card-body text-center p-3">
                            <div class="stats-icon bg-danger text-white mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-notes-medical"></i>
                            </div>
                            <h4 class="text-danger mb-1 fw-bold">{{ $stats['sakit'] }}</h4>
                            <small class="text-muted fw-medium">Sakit</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stats-card bg-secondary bg-opacity-10 border-0">
                        <div class="card-body text-center p-3">
                            <div class="stats-icon bg-secondary text-white mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <h4 class="text-secondary mb-1 fw-bold">{{ $stats['alpha'] }}</h4>
                            <small class="text-muted fw-medium">Belum Absen</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stats-card bg-primary text-white border-0">
                        <div class="card-body text-center p-3">
                            <div class="stats-icon bg-white bg-opacity-20 mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <h4 class="mb-1 fw-bold">{{ $sesiAbsensi->absensi->count() }}</h4>
                            <small class="opacity-75 fw-medium">Total Terdaftar</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                @php
                    $totalHadir = ($stats['hadir_fingerprint'] ?? 0) + ($stats['hadir_manual'] ?? 0);
                    $totalMhs = $sesiAbsensi->absensi->count();
                    $persentaseHadir = $totalMhs > 0 ? round(($totalHadir / $totalMhs) * 100, 1) : 0;
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-medium">Tingkat Kehadiran</span>
                    <span class="fw-bold text-success">{{ $persentaseHadir }}%</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 10px;">
                    <div class="progress-bar bg-success" style="width: {{ $persentaseHadir }}%; border-radius: 10px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Mahasiswa + Status -->
    <div class="card info-card mb-4">
        <div class="card-header border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-users text-info me-2"></i>
                    Daftar Kehadiran Mahasiswa
                </h5>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <input type="text" id="searchMahasiswa" class="form-control search-box" 
                           placeholder="🔍 Cari nama atau NIM..." style="width: 250px;">
                    <select id="filterStatus" class="form-select" style="width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Belum Absen</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern" id="mahasiswaTable">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th width="120">Status</th>
                            <th width="120">Waktu</th>
                            <th width="80">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sesiAbsensi->absensi as $index => $absensi)
                        <tr data-nama="{{ strtolower($absensi->mahasiswa->name ?? '') }}" 
                            data-nim="{{ strtolower($absensi->mahasiswa->no_induk ?? '') }}"
                            data-status="{{ $absensi->status }}">
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $absensi->mahasiswa->no_induk ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <span class="fw-medium">{{ $absensi->mahasiswa->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($absensi->status == 'hadir')
                                    <span class="badge bg-success status-badge">
                                        <i class="fas fa-check me-1"></i>Hadir
                                    </span>
                                @elseif($absensi->status == 'izin')
                                    <span class="badge bg-warning status-badge">
                                        <i class="fas fa-file-alt me-1"></i>Izin
                                    </span>
                                @elseif($absensi->status == 'sakit')
                                    <span class="badge bg-danger status-badge">
                                        <i class="fas fa-notes-medical me-1"></i>Sakit
                                    </span>
                                @else
                                    <span class="badge bg-secondary status-badge">
                                        <i class="fas fa-clock me-1"></i>Belum Absen
                                    </span>
                                @endif
                            </td>
                            {{-- Waktu Kehadiran (waktu mahasiswa berhasil absen) --}}
                            <td class="text-center">
                                @if($absensi->status != 'alpha' && $absensi->waktu_scan)
                                    <span class="fw-medium text-success">{{ $absensi->waktu_scan->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($absensi->foto)
                                    <img src="{{ asset('storage/foto_absensi/' . $absensi->foto) }}" 
                                         alt="Foto" class="rounded-circle border" 
                                         style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                         data-bs-toggle="modal" data-bs-target="#fotoModal{{ $absensi->id }}">
                                @else
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-camera text-muted"></i>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada data yang ditemukan</h5>
                <p class="text-muted">Coba ubah kata kunci pencarian atau filter status</p>
            </div>
        </div>
    </div>

    <!-- Absensi Manual -->
    @if($sesiAbsensi->status == 'aktif')
    <div class="card info-card mb-4">
        <div class="card-header border-0 pb-0">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-user-edit text-warning me-2"></i>
                Absensi Manual
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between p-3 bg-warning bg-opacity-10 rounded">
                <div>
                    <h6 class="mb-1 fw-bold">Kelola Absensi Mahasiswa</h6>
                    <p class="mb-0 text-muted">Ubah status kehadiran mahasiswa yang belum melakukan absensi otomatis</p>
                </div>
                <button type="button" class="btn btn-warning btn-modern" data-bs-toggle="modal" data-bs-target="#absensiManualModal">
                    <i class="fas fa-user-edit me-2"></i>Buka Absensi Manual
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Absensi Manual -->
    <div class="modal fade" id="absensiManualModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <div>
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-user-edit text-warning me-2"></i>
                            Absensi Manual Mahasiswa
                        </h5>
                        <small class="text-muted">Pilih mahasiswa dan ubah status kehadiran</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dosen.jadwal-mengajar.absen-manual', $sesiAbsensi->id) }}" method="POST" id="formAbsensiManual">
                    @csrf
                    <div class="modal-body">
                        <!-- Search Box -->
                        <div class="mb-3">
                            <input type="text" id="searchModalMahasiswa" class="form-control search-box" 
                                   placeholder="🔍 Cari nama atau NIM mahasiswa...">
                        </div>
                        
                        <!-- Info Summary -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="p-2 bg-success bg-opacity-10 rounded text-center">
                                    <small class="text-muted d-block">Hadir</small>
                                    <strong class="text-success" id="countHadir">0</strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 bg-warning bg-opacity-10 rounded text-center">
                                    <small class="text-muted d-block">Izin</small>
                                    <strong class="text-warning" id="countIzin">0</strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 bg-danger bg-opacity-10 rounded text-center">
                                    <small class="text-muted d-block">Sakit</small>
                                    <strong class="text-danger" id="countSakit">0</strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 bg-secondary bg-opacity-10 rounded text-center">
                                    <small class="text-muted d-block">Alpha</small>
                                    <strong class="text-secondary" id="countAlpha">0</strong>
                                </div>
                            </div>
                        </div>

                        @php
                            $totalAbsensi = $sesiAbsensi->absensi->count();
                        @endphp
                        
                        @if($totalAbsensi > 0)
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Ditemukan <strong>{{ $totalAbsensi }}</strong> data absensi mahasiswa
                        </div>
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-hover table-modern table-bordered" id="modalMahasiswaTable">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th width="50" class="text-center">No</th>
                                        <th width="120">NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th width="80" class="text-center">Hadir</th>
                                        <th width="80" class="text-center">Izin</th>
                                        <th width="80" class="text-center">Sakit</th>
                                        <th width="80" class="text-center">Alpha</th>
                                        <th width="100" class="text-center">Metode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sesiAbsensi->absensi as $index => $absensi)
                                    @php
                                        $mahasiswaId = $absensi->id_mahasiswa ?? $absensi->mahasiswa_id;
                                        $currentStatus = $absensi->status ?? 'alpha';
                                        $isOtomatis = $absensi->metode == 'fingerprint' || $absensi->metode == 'camera';
                                    @endphp
                                    <tr class="modal-row" 
                                        data-nama="{{ strtolower($absensi->mahasiswa->name ?? '') }}" 
                                        data-nim="{{ strtolower($absensi->mahasiswa->no_induk ?? '') }}"
                                        data-status="{{ $currentStatus }}">
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $absensi->mahasiswa->no_induk ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="fas fa-user text-primary"></i>
                                                </div>
                                                <span class="fw-medium">{{ $absensi->mahasiswa->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" 
                                                   name="status_{{ $mahasiswaId }}" 
                                                   value="hadir" 
                                                   class="form-check-input status-radio status-hadir" 
                                                   data-mahasiswa-id="{{ $mahasiswaId }}"
                                                   {{ $currentStatus == 'hadir' ? 'checked' : '' }}
                                                   {{ $isOtomatis && $currentStatus == 'hadir' ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" 
                                                   name="status_{{ $mahasiswaId }}" 
                                                   value="izin" 
                                                   class="form-check-input status-radio status-izin" 
                                                   data-mahasiswa-id="{{ $mahasiswaId }}"
                                                   {{ $currentStatus == 'izin' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" 
                                                   name="status_{{ $mahasiswaId }}" 
                                                   value="sakit" 
                                                   class="form-check-input status-radio status-sakit" 
                                                   data-mahasiswa-id="{{ $mahasiswaId }}"
                                                   {{ $currentStatus == 'sakit' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" 
                                                   name="status_{{ $mahasiswaId }}" 
                                                   value="alpha" 
                                                   class="form-check-input status-radio status-alpha" 
                                                   data-mahasiswa-id="{{ $mahasiswaId }}"
                                                   {{ $currentStatus == 'alpha' || !$currentStatus ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            @if($isOtomatis)
                                                <span class="badge bg-success bg-opacity-10 text-success">
                                                    <i class="fas fa-fingerprint"></i> Auto
                                                </span>
                                            @elseif($absensi->metode == 'manual')
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    <i class="fas fa-hand-paper"></i> Manual
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Info Footer -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Total Mahasiswa: <strong>{{ $sesiAbsensi->absensi->count() }}</strong>
                                </span>
                                <span class="text-muted">
                                    <i class="fas fa-lock text-success me-1"></i>
                                    Absensi otomatis (Hadir) tidak dapat diubah
                                </span>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak Ada Mahasiswa Terdaftar</h5>
                            <p class="text-muted">Belum ada mahasiswa yang terdaftar di pertemuan ini</p>
                        </div>
                        @endif

                        <!-- Empty State for Search -->
                        <div id="emptyStateModal" class="text-center py-5" style="display: none;">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada mahasiswa ditemukan</h5>
                            <p class="text-muted">Coba ubah kata kunci pencarian</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Tutup
                        </button>
                        @if($sesiAbsensi->absensi->count() > 0)
                        <button type="submit" class="btn btn-primary" id="btnSubmitAbsensi">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search and Filter functionality
    function filterTable() {
        const searchTerm = $('#searchMahasiswa').val().toLowerCase();
        const statusFilter = $('#filterStatus').val();
        let visibleRows = 0;
        
        $('#mahasiswaTable tbody tr').each(function() {
            const nama = $(this).data('nama');
            const nim = $(this).data('nim');
            const status = $(this).data('status');
            
            const matchesSearch = nama.includes(searchTerm) || nim.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesStatus) {
                $(this).show();
                visibleRows++;
            } else {
                $(this).hide();
            }
        });
        
        // Show/hide empty state
        if (visibleRows === 0) {
            $('#emptyState').show();
            $('#mahasiswaTable').hide();
        } else {
            $('#emptyState').hide();
            $('#mahasiswaTable').show();
        }
        
        // Update row numbers for visible rows
        let rowNumber = 1;
        $('#mahasiswaTable tbody tr:visible').each(function() {
            $(this).find('td:first .badge').text(rowNumber++);
        });
    }
    
    // Bind search and filter events
    $('#searchMahasiswa').on('input', filterTable);
    $('#filterStatus').on('change', filterTable);
    
    // ===== MODAL ABSENSI MANUAL FUNCTIONALITY =====
    
    // Function to update status counts
    function updateStatusCounts() {
        const countHadir = $('.status-radio:checked[value="hadir"]').length;
        const countIzin = $('.status-radio:checked[value="izin"]').length;
        const countSakit = $('.status-radio:checked[value="sakit"]').length;
        const countAlpha = $('.status-radio:checked[value="alpha"]').length;
        
        $('#countHadir').text(countHadir);
        $('#countIzin').text(countIzin);
        $('#countSakit').text(countSakit);
        $('#countAlpha').text(countAlpha);
    }
    
    // Initialize counts when modal opens
    $('#absensiManualModal').on('shown.bs.modal', function() {
        updateStatusCounts();
    });
    
    // Update counts when radio button changes
    $('.status-radio').on('change', function() {
        updateStatusCounts();
        
        // Highlight changed rows
        const row = $(this).closest('tr');
        const originalStatus = row.data('status');
        const newStatus = $(this).val();
        
        if (originalStatus !== newStatus) {
            row.addClass('table-warning');
        } else {
            row.removeClass('table-warning');
        }
    });
    
    // Search functionality in modal
    $('#searchModalMahasiswa').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        let visibleRows = 0;
        
        $('#modalMahasiswaTable tbody tr').each(function() {
            const nama = $(this).data('nama');
            const nim = $(this).data('nim');
            
            if (nama.includes(searchTerm) || nim.includes(searchTerm)) {
                $(this).show();
                visibleRows++;
            } else {
                $(this).hide();
            }
        });
        
        // Show/hide empty state
        if (visibleRows === 0) {
            $('#emptyStateModal').show();
            $('#modalMahasiswaTable').parent().hide();
        } else {
            $('#emptyStateModal').hide();
            $('#modalMahasiswaTable').parent().show();
        }
        
        // Update row numbers for visible rows
        let rowNumber = 1;
        $('#modalMahasiswaTable tbody tr:visible').each(function() {
            $(this).find('td:first .badge').text(rowNumber++);
        });
    });
    
    // Form validation before submit
    $('#formAbsensiManual').on('submit', function(e) {
        e.preventDefault(); // Prevent default first
        
        // Collect all mahasiswa IDs that need to be updated
        const updates = [];
        
        $('.status-radio:checked').each(function() {
            const mahasiswaId = $(this).data('mahasiswa-id');
            const status = $(this).val();
            const row = $(this).closest('tr');
            const originalStatus = row.data('status');
            
            // Only include if status changed
            if (status !== originalStatus) {
                updates.push({
                    id: mahasiswaId,
                    status: status
                });
            }
        });
        
        console.log('Updates to be sent:', updates);
        console.log('Total changes:', updates.length);
        
        if (updates.length === 0) {
            alert('Tidak ada perubahan yang perlu disimpan!');
            return false;
        }
        
        // Confirm before submit
        const confirmMsg = `Anda akan mengubah status ${updates.length} mahasiswa. Lanjutkan?`;
        if (!confirm(confirmMsg)) {
            return false;
        }
        
        // Show loading state
        const submitBtn = $('#btnSubmitAbsensi');
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        submitBtn.prop('disabled', true);
        
        // Submit the form
        this.submit();
    });
    
    // Reset modal when closed
    $('#absensiManualModal').on('hidden.bs.modal', function() {
        $('#searchModalMahasiswa').val('');
        $('.modal-row').show().removeClass('table-warning');
        $('#emptyStateModal').hide();
        $('#modalMahasiswaTable').parent().show();
        
        // Reset radio buttons to original state
        $('.status-radio').each(function() {
            const row = $(this).closest('tr');
            const originalStatus = row.data('status');
            const radioValue = $(this).val();
            
            if (radioValue === originalStatus) {
                $(this).prop('checked', true);
            }
        });
        
        updateStatusCounts();
        
        // Reset submit button
        const submitBtn = $('#btnSubmitAbsensi');
        submitBtn.html('<i class="fas fa-save me-2"></i>Simpan Perubahan');
        submitBtn.prop('disabled', false);
    });
    
    // Smooth animations for stats cards
    $('.stats-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
    // Auto refresh statistics every 2 minutes (less aggressive)
    let refreshInterval = setInterval(function() {
        // Only refresh if no modal is open and no input is focused
        if (!$('.modal').hasClass('show') && !$('input:focus, select:focus').length) {
            location.reload();
        }
    }, 120000); // 2 minutes instead of 30 seconds
    
    // Pause auto-refresh when user is interacting
    $('input, select, button').on('focus click', function() {
        clearInterval(refreshInterval);
        
        // Resume after 5 minutes of inactivity
        setTimeout(function() {
            refreshInterval = setInterval(function() {
                if (!$('.modal').hasClass('show') && !$('input:focus, select:focus').length) {
                    location.reload();
                }
            }, 120000);
        }, 300000); // 5 minutes
    });
    
    // Show loading state for better UX
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
        submitBtn.prop('disabled', true);
        
        // Re-enable after 5 seconds as fallback
        setTimeout(function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 5000);
    });
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endpush
