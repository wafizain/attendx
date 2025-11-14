@extends('layouts.master')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Riwayat Absensi</h2>
            <p class="text-muted">Lihat riwayat kehadiran Anda</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-success mb-2">Hadir</h6>
                    <h3 class="mb-0 text-success">{{ $stats['hadir'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-info mb-2">Izin</h6>
                    <h3 class="mb-0 text-info">{{ $stats['izin'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-warning mb-2">Sakit</h6>
                    <h3 class="mb-0 text-warning">{{ $stats['sakit'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-danger mb-2">Alpha</h6>
                    <h3 class="mb-0 text-danger">{{ $stats['alpha'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-primary mb-2">Persentase</h6>
                    <h3 class="mb-0 text-primary">{{ $stats['persentase'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('mahasiswa.absensi') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }} - {{ $kelas->mataKuliah ? $kelas->mataKuliah->nama_matkul : 'Mata Kuliah Tidak Ditemukan' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Absensi List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Mata Kuliah</th>
                            <th>Kelas</th>
                            <th>Pertemuan</th>
                            <th>Status</th>
                            <th>Waktu Absen</th>
                            <th>Metode</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensiList as $index => $absensi)
                        <tr>
                            <td>{{ $absensiList->firstItem() + $index }}</td>
                            <td>{{ $absensi->sesiAbsensi->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $absensi->sesiAbsensi->kelas->mataKuliah ? $absensi->sesiAbsensi->kelas->mataKuliah->nama_matkul : 'Mata Kuliah Tidak Ditemukan' }}</td>
                            <td>{{ $absensi->sesiAbsensi->kelas->nama_kelas }}</td>
                            <td>{{ $absensi->sesiAbsensi->pertemuan_ke ?? '-' }}</td>
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
                                    {{ \Carbon\Carbon::parse($absensi->waktu_absen)->format('H:i:s') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($absensi->metode_absen == 'fingerprint')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-fingerprint"></i> Sidik Jari
                                    </span>
                                @elseif($absensi->metode_absen == 'manual')
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-edit"></i> Manual
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $absensi->keterangan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Belum ada riwayat absensi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $absensiList->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
