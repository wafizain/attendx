@extends('layouts.master')

@section('title', 'Detail Pertemuan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="fas fa-clock-rotate-left me-2"></i>
                Detail Pertemuan
            </h4>
            <p class="text-muted mb-0">Informasi lengkap pertemuan yang telah dilaksanakan</p>
        </div>
        <div>
            <a href="{{ route('dosen.riwayat-pertemuan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            @if($pertemuan->sesiAbsensi)
            <a href="{{ route('dosen.riwayat-pertemuan.download', ['id' => $pertemuan->id, 'format' => 'pdf']) }}" 
               class="btn btn-success">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('dosen.riwayat-pertemuan.download', ['id' => $pertemuan->id, 'format' => 'excel']) }}" 
               class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Download Excel
            </a>
            @endif
        </div>
    </div>

    <!-- Informasi Pertemuan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Informasi Pertemuan
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Mata Kuliah</strong></td>
                            <td>{{ $pertemuan->jadwal->mataKuliah->nama_mk }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kelas</strong></td>
                            <td>{{ $pertemuan->jadwal->kelas->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ruangan</strong></td>
                            <td>{{ $pertemuan->jadwal->ruangan->nama }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pertemuan Ke</strong></td>
                            <td>{{ $pertemuan->pertemuan_ke }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Tanggal</strong></td>
                            <td>{{ \Carbon\Carbon::parse($pertemuan->tanggal)->locale('id')->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu</strong></td>
                            <td>{{ $pertemuan->jadwal->jam_mulai }} - {{ $pertemuan->jadwal->jam_selesai }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                @if($pertemuan->status_sesi == 'selesai')
                                <span class="badge bg-success">Selesai</span>
                                @elseif($pertemuan->status_sesi == 'dibatalkan')
                                <span class="badge bg-danger">Dibatalkan</span>
                                @else
                                <span class="badge bg-warning">{{ $pertemuan->status_sesi }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dosen</strong></td>
                            <td>{{ Auth::user()->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Kehadiran -->
    @if($pertemuan->sesiAbsensi && isset($stats))
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>
                Statistik Kehadiran
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-success text-white rounded p-3">
                        <h4 class="mb-1">{{ $stats['hadir'] }}</h4>
                        <small>Hadir</small>
                        <div class="mt-1">
                            <small>Fingerprint: {{ $stats['hadir_fingerprint'] }}</small><br>
                            <small>Manual: {{ $stats['hadir_manual'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-warning text-white rounded p-3">
                        <h4 class="mb-1">{{ $stats['izin'] }}</h4>
                        <small>Izin</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-danger text-white rounded p-3">
                        <h4 class="mb-1">{{ $stats['sakit'] }}</h4>
                        <small>Sakit</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-secondary text-white rounded p-3">
                        <h4 class="mb-1">{{ $stats['alpha'] }}</h4>
                        <small>Alpha</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-primary text-white rounded p-3">
                        <h4 class="mb-1">{{ $stats['total'] }}</h4>
                        <small>Total</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-info text-white rounded p-3">
                        <h4 class="mb-1">{{ $stats['persentase_kehadiran'] }}%</h4>
                        <small>Kehadiran</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Daftar Kehadiran Mahasiswa -->
    @if($pertemuan->sesiAbsensi)
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Daftar Kehadiran Mahasiswa
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Status</th>
                            <th>Waktu Absen</th>
                            <th>Metode</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pertemuan->sesiAbsensi->absensi->sortBy('mahasiswa.nama') as $index => $absensi)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $absensi->mahasiswa->nim }}</td>
                            <td>{{ $absensi->mahasiswa->nama }}</td>
                            <td>
                                @if($absensi->status == 'hadir')
                                <span class="badge bg-success">Hadir</span>
                                @elseif($absensi->status == 'izin')
                                <span class="badge bg-warning">Izin</span>
                                @elseif($absensi->status == 'sakit')
                                <span class="badge bg-danger">Sakit</span>
                                @else
                                <span class="badge bg-secondary">Alpha</span>
                                @endif
                            </td>
                            <td>
                                @if($absensi->waktu_absen)
                                {{ \Carbon\Carbon::parse($absensi->waktu_absen)->format('H:i') }}
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($absensi->metode == 'fingerprint')
                                <span class="badge bg-info">Fingerprint</span>
                                @elseif($absensi->metode == 'manual')
                                <span class="badge bg-primary">Manual</span>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($absensi->foto)
                                <img src="{{ asset('storage/' . $absensi->foto) }}" 
                                     alt="Foto" class="img-thumbnail" style="max-width: 50px; max-height: 50px;"
                                     data-bs-toggle="modal" data-bs-target="#fotoModal{{ $absensi->id }}">
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Foto -->
                        @if($absensi->foto)
                        <div class="modal fade" id="fotoModal{{ $absensi->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Foto Bukti Kehadiran</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="{{ asset('storage/' . $absensi->foto) }}" 
                                             alt="Foto" class="img-fluid rounded">
                                        <p class="mt-2 mb-0">
                                            <strong>{{ $absensi->mahasiswa->nama }}</strong><br>
                                            <small>{{ \Carbon\Carbon::parse($absensi->waktu_absen)->format('d M Y H:i:s') }}</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-3">
                                <span class="text-muted">Tidak ada data kehadiran</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
