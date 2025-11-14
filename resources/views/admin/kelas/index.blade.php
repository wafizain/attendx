@extends('layouts.master')

@push('styles')
<style>
  /* Scoped to Kelas index only */
  .kelas-table-wrapper .modern-table { border-collapse: separate; border-spacing: 0 8px; }
  .kelas-table-wrapper thead th { background:#F8FAFC; color:#374151; font-weight:600; border:none !important; padding:12px 16px; }
  .kelas-table-wrapper tbody tr { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); }
  .kelas-table-wrapper tbody tr:hover { box-shadow:0 4px 10px rgba(0,0,0,.08); transform: translateY(-1px); }
  .kelas-table-wrapper tbody td { border:none !important; padding:14px 16px; vertical-align: middle; }
  .kelas-table-wrapper tbody tr td:first-child { border-top-left-radius:12px; border-bottom-left-radius:12px; }
  .kelas-table-wrapper tbody tr td:last-child { border-top-right-radius:12px; border-bottom-right-radius:12px; }
  .kelas-table-wrapper .table-actions .btn { border:1px solid #E5E7EB; background:#fff; color:#374151; }
  .kelas-table-wrapper .table-actions .btn:hover { background:#F3F4F6; }
  .kelas-table-wrapper .card-header { background:#fff; border-bottom:1px solid #E5E7EB; font-weight:600; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Kelas</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <a href="{{ route('kelas.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Kelas
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('kelas.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama kelas..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prodi</label>
                    <select name="prodi_id" class="form-select">
                        <option value="">Semua Prodi</option>
                        @foreach($prodiList as $prodi)
                            <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->nama_prodi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Angkatan</label>
                    <select name="angkatan" class="form-select">
                        <option value="">Semua Angkatan</option>
                        @foreach($angkatanList as $angkatan)
                            <option value="{{ $angkatan }}" {{ request('angkatan') == $angkatan ? 'selected' : '' }}>
                                {{ $angkatan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('kelas.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- Summary under filters -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Kelas: <strong>{{ $kelasList->total() }}</strong></div>
            
        </div>
        <div class="table-responsive kelas-table-wrapper">
            <table class="table modern-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode</th>
                        <th>Nama Kelas</th>
                        <th>Prodi</th>
                        <th>Angkatan</th>
                        <th>Mahasiswa</th>
                        <th>Status</th>
                        <th width="250">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasList as $kelas)
                    <tr>
                        <td class="fw-medium">{{ ($kelasList->firstItem() ?? 0) + $loop->index }}</td>
                        <td><strong>{{ $kelas->kode }}</strong></td>
                        <td>{{ $kelas->nama }}</td>
                        <td>{{ $kelas->prodi->nama ?? '-' }}</td>
                        <td>{{ $kelas->angkatan }}</td>
                        <td>
                            <a href="{{ route('kelas.members', $kelas->id) }}" class="text-decoration-none">
                                {{ $kelas->jumlah_mahasiswa_aktif }}
                                @if($kelas->kapasitas)
                                    / {{ $kelas->kapasitas }}
                                @endif
                            </a>
                        </td>
                        <td>{{ $kelas->status_label }}</td>
                        <td>
                            <div class="btn-group btn-group-sm table-actions" role="group">
                                <a href="{{ route('kelas.show', $kelas->id) }}" class="btn" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('kelas.members', $kelas->id) }}" class="btn" title="Anggota">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="{{ route('kelas.edit', $kelas->id) }}" class="btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('kelas.destroy', $kelas->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn" onclick="confirmDelete({{ $kelas->id }}, '{{ $kelas->nama }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $kelasList->firstItem() }} - {{ $kelasList->lastItem() }} dari {{ $kelasList->total() }} data
            </div>
            <div>
                {{ $kelasList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('kelas.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Kelas dari CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Format: kode, nama, prodi_id, angkatan, kapasitas, wali_dosen_id, status</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Jika kode sudah ada untuk prodi & angkatan yang sama, data akan diupdate.
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        confirmButtonColor: '#0e4a95'
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: @json(session('error')),
        confirmButtonColor: '#0e4a95'
    });
    @endif
});

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Kelas?',
        text: 'Yakin ingin menghapus kelas "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("kelas.destroy", ":id") }}'.replace(':id', id);
            
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
