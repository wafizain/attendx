@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $mataKuliah->nama_mk }}</h1>
            <p class="text-muted mb-0">{{ $mataKuliah->kode_mk }} | {{ $mataKuliah->prodi->nama ?? '-' }} | {{ $mataKuliah->kurikulum }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahPengampuModal">
                <i class="fas fa-plus"></i> Tambah Pengampu
            </button>
            <a href="{{ route('mata-kuliah.edit', $mataKuliah->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$mataKuliah->status && $statistik['total_jadwal_aktif'] > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Peringatan!</strong> Mata kuliah ini nonaktif tetapi masih memiliki {{ $statistik['total_jadwal_aktif'] }} jadwal aktif.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jadwal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_jadwal'] }}</div>
                            <small class="text-muted">Aktif: {{ $statistik['total_jadwal_aktif'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pengampu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_pengampu'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Kelas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_kelas'] }}</div>
                            <small class="text-muted">Aktif: {{ $statistik['total_kelas_aktif'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Mahasiswa</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_mahasiswa'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="mkTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-info-circle"></i> Profil MK
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="jadwal-tab" data-bs-toggle="tab" data-bs-target="#jadwal" type="button" role="tab">
                        <i class="fas fa-calendar-alt"></i> Jadwal Aktif
                        @if($statistik['total_jadwal_aktif'] > 0)
                            <span class="badge bg-primary">{{ $statistik['total_jadwal_aktif'] }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                        <i class="fas fa-history"></i> Log Aktivitas
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="mkTabsContent">
                <!-- Tab 1: Profil MK -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Informasi Dasar</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="180">Kode MK</th>
                                    <td><strong class="text-primary">{{ $mataKuliah->kode_mk }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Nama MK</th>
                                    <td><strong>{{ $mataKuliah->nama_mk }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td>
                                        @if($mataKuliah->prodi)
                                            {{ $mataKuliah->prodi->nama }} ({{ $mataKuliah->prodi->jenjang }})
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Kurikulum</th>
                                    <td><span class="badge bg-secondary">{{ $mataKuliah->kurikulum }}</span></td>
                                </tr>
                                <tr>
                                    <th>SKS</th>
                                    <td><strong>{{ $mataKuliah->sks }}</strong> SKS</td>
                                </tr>
                                <tr>
                                    <th>Semester</th>
                                    <td>{{ $mataKuliah->semester_rekomendasi ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-{{ $mataKuliah->status_badge }}">{{ $mataKuliah->status_label }}</span>
                                    </td>
                                </tr>
                                @if($mataKuliah->kode_eksternal)
                                <tr>
                                    <th>Kode Eksternal</th>
                                    <td><code>{{ $mataKuliah->kode_eksternal }}</code></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Informasi Tambahan</h5>
                            @if($mataKuliah->deskripsi)
                            <div class="mb-3">
                                <strong>Deskripsi:</strong>
                                <p class="text-muted">{{ $mataKuliah->deskripsi }}</p>
                            </div>
                            @endif
                            @if($mataKuliah->prasyarat)
                            <div class="mb-3">
                                <strong>Prasyarat:</strong><br>
                                @foreach($mataKuliah->prasyarat as $prasyarat)
                                    <span class="badge bg-info">{{ $prasyarat }}</span>
                                @endforeach
                            </div>
                            @endif

                            <!-- Daftar Pengampu -->
                            @if($mataKuliah->pengampu->count() > 0)
                            <div class="mb-3">
                                <strong>Dosen Pengampu:</strong>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama Dosen</th>
                                                <th>Peran</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mataKuliah->pengampu as $pengampu)
                                            <tr>
                                                <td>
                                                    {{ $pengampu->dosen->name ?? '-' }}
                                                    <small class="text-muted">({{ $pengampu->dosen->no_induk ?: $pengampu->dosen->username ?: '-' }})</small>
                                                </td>
                                                <td><span class="badge bg-primary">{{ $pengampu->peran }}</span></td>
                                                <td>
                                                    <form action="{{ route('mata-kuliah.remove-pengampu', [$mataKuliah->id, $pengampu->id]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeletePengampu(this)">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada dosen pengampu.
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#tambahPengampuModal">
                                    <i class="fas fa-plus"></i> Tambahkan sekarang
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Jadwal Aktif -->
                <div class="tab-pane fade" id="jadwal" role="tabpanel">
                    @if($jadwalAktif->total() > 0)
                        @if(!$mataKuliah->status)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Peringatan!</strong> Mata kuliah ini nonaktif tetapi masih memiliki {{ $jadwalAktif->total() }} jadwal aktif.
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Hari</th>
                                        <th>Jam</th>
                                        <th>Ruangan</th>
                                        <th>Dosen</th>
                                        <th>Kelas</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jadwalAktif as $index => $jadwal)
                                    <tr>
                                        <td>{{ ($jadwalAktif->currentPage() - 1) * $jadwalAktif->perPage() + $index + 1 }}</td>
                                        <td>{{ $jadwal->hari_nama }}</td>
                                        <td>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                                        <td>{{ $jadwal->ruangan->nama ?? '-' }}</td>
                                        <td>{{ $jadwal->dosen->name ?? '-' }}</td>
                                        <td>{{ $jadwal->kelas->nama ?? $jadwal->kelas->nama_kelas ?? '-' }}</td>
                                        <td>
                                            <small>{{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d/m/Y') : '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ ucfirst($jadwal->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('jadwal.show', $jadwal->id) }}" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($jadwalAktif->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $jadwalAktif->links('pagination::bootstrap-5') }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada jadwal aktif</h5>
                            <p class="text-muted">Belum ada jadwal kuliah aktif untuk mata kuliah ini.</p>
                        </div>
                    @endif
                </div>

                <!-- Tab 3: Log Aktivitas -->
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    @if($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="180">Waktu</th>
                                        <th width="150">User</th>
                                        <th>Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $log->user->name ?? 'System' }}</td>
                                        <td>{{ $log->description }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada log aktivitas</h5>
                            <p class="text-muted">Belum ada aktivitas yang tercatat untuk mata kuliah ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengampu -->
<div class="modal fade" id="tambahPengampuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('mata-kuliah.add-pengampu', $mataKuliah->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengampu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dosen <span class="text-danger">*</span></label>
                        <select name="id_dosen" class="form-select" required>
                            <option value="">-- Pilih Dosen --</option>
                            @foreach($availableDosen as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->name }} ({{ $dosen->no_induk ?: $dosen->username ?: '-' }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya dosen aktif yang belum menjadi pengampu</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Pengampu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Swal is available
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        return;
    }
    
    // Check for success message in session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            confirmButtonColor: '#0e4a95'
        });
    @endif

    // Check for error message in session
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session("error") }}',
            confirmButtonColor: '#0e4a95'
        });
    @endif

    // Refresh page after modal closes if there was a success
    document.getElementById('tambahPengampuModal').addEventListener('hidden.bs.modal', function () {
        // Check if there was a recent success message (indicates recent operation)
        if (document.querySelector('.alert-success')) {
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    });
});

// Function to confirm pengampu deletion
function confirmDeletePengampu(button) {
    event.preventDefault();
    console.log('confirmDeletePengampu called');
    
    Swal.fire({
        title: 'Hapus Pengampu?',
        text: 'Apakah Anda yakin ingin menghapus dosen pengampu ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Confirmed, submitting form');
            button.form.submit();
        } else {
            console.log('Cancelled');
        }
    });
}
</script>
@endpush

@endsection
