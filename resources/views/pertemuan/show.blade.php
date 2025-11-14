@extends('layouts.master')

@section('title', 'Detail Pertemuan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('pertemuan.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-check text-primary"></i> Detail Pertemuan
            </h1>
            <p class="text-muted mb-0">
                {{ $pertemuan->jadwal->mataKuliah->nama_mk }} - Pertemuan ke-{{ $pertemuan->minggu_ke }}
            </p>
        </div>
        <div>
            @if($pertemuan->status_sesi == 'direncanakan')
            <form action="{{ route('pertemuan.open', $pertemuan->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-door-open"></i> Buka Sesi
                </button>
            </form>
            @endif
            
            @if($pertemuan->status_sesi == 'berjalan')
            <form action="{{ route('pertemuan.close', $pertemuan->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-secondary btn-lg">
                    <i class="fas fa-door-closed"></i> Tutup Sesi
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pertemuan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150"><strong>Mata Kuliah</strong></td>
                                    <td>{{ $pertemuan->jadwal->mataKuliah->nama_mk }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dosen</strong></td>
                                    <td>{{ $pertemuan->jadwal->dosen->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pertemuan</strong></td>
                                    <td>Minggu ke-{{ $pertemuan->minggu_ke }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150"><strong>Tanggal</strong></td>
                                    <td>{{ $pertemuan->tanggal->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jam</strong></td>
                                    <td>{{ $pertemuan->jam_mulai }} - {{ $pertemuan->jam_selesai }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ruangan</strong></td>
                                    <td>{{ $pertemuan->ruangan->nama }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kehadiran -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-users"></i> Daftar Kehadiran</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pertemuan->absensi as $a)
                                <tr>
                                    <td>{{ $a->mahasiswa->nim }}</td>
                                    <td>{{ $a->mahasiswa->nama }}</td>
                                    <td>
                                        @if($a->waktu_scan)
                                        {{ $a->waktu_scan->format('H:i:s') }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if($a->status == 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($a->status == 'telat')
                                            <span class="badge bg-warning">Telat</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($a->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada yang absen</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Statistik -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Statistik</h6>
                </div>
                <div class="card-body">
                    @php
                        $stats = $statistik;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Peserta</span>
                            <strong>{{ $stats['total_peserta'] }}</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['persentase_hadir'] }}%">
                                {{ $stats['persentase_hadir'] }}%
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ $stats['hadir'] }}</h4>
                                <small class="text-muted">Hadir</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ $stats['telat'] }}</h4>
                                <small class="text-muted">Telat</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $stats['izin'] }}</h4>
                                <small class="text-muted">Izin</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0">{{ $stats['alfa'] }}</h4>
                                <small class="text-muted">Alfa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Window Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-clock"></i> Window Absensi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><i class="fas fa-door-open text-success"></i> Buka</td>
                            <td class="text-end"><strong>{{ $window['open']->format('H:i') }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-exclamation-triangle text-warning"></i> Batas Telat</td>
                            <td class="text-end"><strong>{{ $window['late']->format('H:i') }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-door-closed text-danger"></i> Tutup</td>
                            <td class="text-end"><strong>{{ $window['close']->format('H:i') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-refresh untuk monitoring live
@if($pertemuan->status_sesi == 'berjalan')
setInterval(function() {
    location.reload();
}, 15000); // Refresh setiap 15 detik
@endif
</script>
@endpush
