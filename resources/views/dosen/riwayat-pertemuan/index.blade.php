@extends('layouts.master')

@section('title', 'Riwayat Pertemuan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="fas fa-clock-rotate-left me-2"></i>
                Riwayat Pertemuan
            </h4>
            <p class="text-muted mb-0">Daftar semua pertemuan yang telah dilaksanakan</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dosen.riwayat-pertemuan.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mata Kuliah</label>
                        <select name="mata_kuliah" class="form-select">
                            <option value="">Semua Mata Kuliah</option>
                            @foreach($mataKuliahList as $mk)
                            <option value="{{ $mk }}" {{ request('mata_kuliah') == $mk ? 'selected' : '' }}>
                                {{ $mk }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                {{ $kelas }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('dosen.riwayat-pertemuan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Pertemuan List -->
    <div class="card shadow-sm">
        <div class="card-body">
            @forelse($pertemuanList as $pertemuan)
            <div class="border-bottom pb-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="bg-light rounded-circle p-3 text-center">
                                    <i class="fas fa-calendar-check fa-fw text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $pertemuan->jadwal->mataKuliah->nama_mk }}</h6>
                                <div class="row g-2 mb-2">
                                    <div class="col-auto">
                                        <small class="text-muted">Kelas:</small>
                                        <strong>{{ $pertemuan->jadwal->kelas->nama ?? '-' }}</strong>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">Ruangan:</small>
                                        <strong>{{ $pertemuan->jadwal->ruangan->nama }}</strong>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">Pertemuan:</small>
                                        <strong>{{ $pertemuan->pertemuan_ke }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($pertemuan->tanggal)->locale('id')->format('d M Y') }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $pertemuan->jadwal->jam_mulai }} - {{ $pertemuan->jadwal->jam_selesai }}
                                    </span>
                                    @if($pertemuan->sesiAbsensi)
                                    <span class="badge bg-success">
                                        <i class="fas fa-users me-1"></i>
                                        {{ $pertemuan->sesiAbsensi->absensi->where('status', 'hadir')->count() }} Hadir
                                    </span>
                                    <span class="badge bg-info">
                                        {{ round(($pertemuan->sesiAbsensi->absensi->where('status', 'hadir')->count() / $pertemuan->sesiAbsensi->absensi->count()) * 100, 1) }}% Kehadiran
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="mb-2">
                            @if($pertemuan->status_sesi == 'selesai')
                            <span class="badge bg-success">Selesai</span>
                            @elseif($pertemuan->status_sesi == 'dibatalkan')
                            <span class="badge bg-danger">Dibatalkan</span>
                            @else
                            <span class="badge bg-warning">{{ $pertemuan->status_sesi }}</span>
                            @endif
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('dosen.riwayat-pertemuan.show', $pertemuan->id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                            @if($pertemuan->sesiAbsensi)
                            <a href="{{ route('dosen.riwayat-pertemuan.download', ['id' => $pertemuan->id, 'format' => 'pdf']) }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                            <a href="{{ route('dosen.riwayat-pertemuan.download', ['id' => $pertemuan->id, 'format' => 'excel']) }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-calendar-xmark fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum Ada Riwayat Pertemuan</h5>
                <p class="text-muted">Belum ada pertemuan yang telah selesai dilaksanakan</p>
            </div>
            @endforelse

            <!-- Pagination -->
            @if($pertemuanList->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $pertemuanList->firstItem() }} - {{ $pertemuanList->lastItem() }} 
                    dari {{ $pertemuanList->total() }} data
                </div>
                {{ $pertemuanList->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
