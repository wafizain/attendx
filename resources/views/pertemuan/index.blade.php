@extends('layouts.master')

@section('title', 'Daftar Pertemuan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-check text-primary"></i> Daftar Pertemuan
            </h1>
            @if($jadwal)
            <p class="text-muted mb-0">
                {{ $jadwal->mataKuliah->nama_mk }} - {{ $jadwal->dosen->name }} 
                ({{ $jadwal->hari_nama }}, {{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }})
            </p>
            @endif
        </div>
        <div>
            @if($jadwal && auth()->user()->role === 'admin')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-plus-circle"></i> Generate Pertemuan
            </button>
            @endif
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pertemuan.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Sesi</label>
                        <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Status</option>
                            <option value="direncanakan" {{ request('status') == 'direncanakan' ? 'selected' : '' }}>Direncanakan</option>
                            <option value="berjalan" {{ request('status') == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}" onchange="document.getElementById('filterForm').submit()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ruangan</label>
                        <select name="id_ruangan" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Ruangan</option>
                            @foreach(\App\Models\Ruangan::orderBy('nama')->get() as $r)
                            <option value="{{ $r->id }}" {{ request('id_ruangan') == $r->id ? 'selected' : '' }}>{{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <a href="{{ route('pertemuan.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-redo"></i> Reset Filter
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Pertemuan List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Daftar Pertemuan ({{ $pertemuan->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Minggu</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Ruangan</th>
                            <th width="120">Status</th>
                            <th width="150">Kehadiran</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pertemuan as $p)
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $p->minggu_ke }}</span>
                            </td>
                            <td>
                                <strong>{{ $p->jadwal->mataKuliah->nama_mk }}</strong><br>
                                <small class="text-muted">{{ $p->jadwal->mataKuliah->kode_mk }}</small>
                            </td>
                            <td>{{ $p->jadwal->dosen->name }}</td>
                            <td>
                                {{ $p->tanggal->format('d/m/Y') }}<br>
                                <small class="text-muted">{{ $p->tanggal->locale('id')->isoFormat('dddd') }}</small>
                            </td>
                            <td>
                                {{ $p->jam_mulai }} - {{ $p->jam_selesai }}
                            </td>
                            <td>
                                <i class="fas fa-building text-muted"></i> {{ $p->ruangan->nama }}
                            </td>
                            <td>
                                @if($p->status_sesi == 'direncanakan')
                                    <span class="badge bg-info">Direncanakan</span>
                                @elseif($p->status_sesi == 'berjalan')
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle-notch fa-spin"></i> Berjalan
                                    </span>
                                @elseif($p->status_sesi == 'selesai')
                                    <span class="badge bg-secondary">Selesai</span>
                                @else
                                    <span class="badge bg-danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $stats = $p->getStatistikKehadiran();
                                @endphp
                                <small>
                                    <i class="fas fa-check text-success"></i> {{ $stats['hadir'] + $stats['telat'] }}/{{ $stats['total_peserta'] }}
                                    <span class="text-muted">({{ $stats['persentase_hadir'] }}%)</span>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('pertemuan.show', $p->id) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($p->status_sesi == 'direncanakan' && (auth()->user()->role == 'admin' || $p->jadwal->id_dosen == auth()->id()))
                                    <form action="{{ route('pertemuan.open', $p->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Buka Sesi">
                                            <i class="fas fa-door-open"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if($p->status_sesi == 'berjalan' && (auth()->user()->role == 'admin' || $p->jadwal->id_dosen == auth()->id()))
                                    <form action="{{ route('pertemuan.close', $p->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" title="Tutup Sesi">
                                            <i class="fas fa-door-closed"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if(auth()->user()->role == 'admin')
                                    <button type="button" class="btn btn-warning" onclick="openRescheduleModal({{ $p->id }})" title="Reschedule">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada pertemuan ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pertemuan->hasPages())
        <div class="card-footer bg-white">
            {{ $pertemuan->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Generate Modal -->
@if($jadwal && auth()->user()->role === 'admin')
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Pertemuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('jadwal.generate-pertemuan', $jadwal->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pertemuan</label>
                        <input type="number" name="jumlah_pertemuan" class="form-control" value="14" min="1" max="20" required>
                        <small class="text-muted">Default: 14 pertemuan per semester</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Pertemuan akan di-generate otomatis berdasarkan jadwal kuliah. Hari libur nasional akan di-skip.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Auto-refresh untuk monitoring live
@if(request('status') == 'berjalan')
setInterval(function() {
    location.reload();
}, 30000); // Refresh setiap 30 detik
@endif
</script>
@endpush
