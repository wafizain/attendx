@extends('layouts.master')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Kelola Anggota Kelas: {{ $kelas->nama }}</h5>
            <p class="text-muted mb-0">{{ $kelas->kode }} - Angkatan {{ $kelas->angkatan }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-user-plus me-2"></i>Tambah Anggota
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import CSV
            </button>
            <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Info Kelas -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Prodi:</strong> {{ $kelas->prodi->nama ?? '-' }}
                </div>
                <div class="col-md-2">
                    <strong>Angkatan:</strong> {{ $kelas->angkatan }}
                </div>
                <div class="col-md-3">
                    <strong>Kapasitas:</strong> 
                    @if($kelas->kapasitas)
                        {{ $kelas->jumlah_mahasiswa_aktif }} / {{ $kelas->kapasitas }}
                    @else
                        {{ $kelas->jumlah_mahasiswa_aktif }} (unlimited)
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong> 
                    <span class="text-{{ $kelas->status_badge === 'success' ? 'success' : ($kelas->status_badge === 'danger' ? 'danger' : 'secondary') }}">
                        {{ $kelas->status_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('kelas.members', $kelas->id) }}" class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="keluar" {{ request('status') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('kelas.members', $kelas->id) }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- List Anggota -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Anggota ({{ $members->total() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Keluar</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                        <tr>
                            <td>{{ $members->firstItem() + $index }}</td>
                            <td><strong>{{ $member->nim }}</strong></td>
                            <td>{{ $member->mahasiswa->name ?? '-' }}</td>
                            <td>{{ $member->mahasiswa->email ?? '-' }}</td>
                            <td>{{ $member->tanggal_masuk->format('d M Y') }}</td>
                            <td>
                                @if($member->tanggal_keluar)
                                    {{ $member->tanggal_keluar->format('d M Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-{{ $member->status_badge === 'success' ? 'success' : ($member->status_badge === 'danger' ? 'danger' : ($member->status_badge === 'warning' ? 'warning' : 'secondary')) }}">
                                    {{ $member->status_label }}
                                </span>
                            </td>
                            <td>{{ $member->keterangan ?? '-' }}</td>
                            <td>
                                @if($member->isAktif())
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#removeModal{{ $member->id }}"
                                            title="Keluarkan"
                                            style="background-color:#dc3545;border-color:#dc3545;">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Remove Member Modal -->
                        @if($member->isAktif())
                        <div class="modal fade" id="removeModal{{ $member->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('kelas.remove-member', [$kelas->id, $member->id]) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Keluarkan Anggota</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Keluarkan <strong>{{ $member->mahasiswa->name }}</strong> dari kelas ini?</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Tanggal Keluar <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_keluar" class="form-control" 
                                                       value="{{ date('Y-m-d') }}" 
                                                       min="{{ $member->tanggal_masuk->format('Y-m-d') }}" 
                                                       max="{{ date('Y-m-d') }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="keterangan" class="form-control" rows="3" 
                                                          placeholder="Alasan keluar..."></textarea>
                                            </div>

                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                Data tidak akan dihapus, hanya diset tanggal keluar untuk histori.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color:#6C757D;border-color:#6C757D;">Batal</button>
                                            <button type="submit" class="btn btn-danger" style="background-color:#dc3545;border-color:#dc3545;">Keluarkan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Tidak ada anggota</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $members->links() }}
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('kelas.add-member', $kelas->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Mahasiswa <span class="text-danger">*</span></label>
                        <select name="nim" class="form-select" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($availableMahasiswa as $mhs)
                                <option value="{{ $mhs->nim }}">
                                    {{ $mhs->nim }} - {{ $mhs->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya mahasiswa dari prodi {{ $kelas->prodi->nama ?? '-' }} yang belum jadi anggota</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_masuk" class="form-control" 
                               value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" 
                                  placeholder="Keterangan tambahan..."></textarea>
                    </div>

                    @if($kelas->kapasitas && $kelas->isFull())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Kelas sudah penuh ({{ $kelas->kapasitas }} mahasiswa)
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color:#6C757D;border-color:#6C757D;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('kelas.import-members', $kelas->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Anggota dari CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Format: nim, tanggal_masuk, keterangan</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Mahasiswa yang sudah menjadi anggota aktif akan di-skip.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color:#6C757D;border-color:#6C757D;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
