@extends('layouts.master')

@push('styles')
<style>
  /* Scoped to Mata Kuliah index only */
  .mk-table-wrapper .modern-table {
    border-collapse: separate;
    border-spacing: 0 8px;
  }
  .mk-table-wrapper .modern-table thead th {
    background: #F8FAFC;
    color: #374151;
    font-weight: 600;
    border: none !important;
    padding: 12px 16px;
  }
  .mk-table-wrapper .modern-table tbody tr {
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  }
  .mk-table-wrapper .modern-table tbody tr:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transform: translateY(-1px);
  }
  .mk-table-wrapper .modern-table tbody td {
    border: none !important;
    padding: 14px 16px;
    vertical-align: middle;
  }
  .mk-table-wrapper .modern-table tbody tr td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  .mk-table-wrapper .modern-table tbody tr td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  .mk-table-wrapper .table-actions .btn {
    border: 1px solid #E5E7EB;
    background: #ffffff;
    color: #374151;
  }
  .mk-table-wrapper .table-actions .btn:hover {
    background: #F3F4F6;
  }
  .mk-table-wrapper .card-header {
    background: #ffffff;
    border-bottom: 1px solid #E5E7EB;
    font-weight: 600;
  }
</style>
@endpush

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Mata Kuliah</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <a href="{{ route('mata-kuliah.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Mata Kuliah
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('mata-kuliah.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama mata kuliah..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Program Studi</label>
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
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="">Semua Semester</option>
                        @foreach([1,2,3,4,5,6,7,8] as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>Semester {{ $sem }}</option>
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
                        <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- Summary under filters -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Mata Kuliah: <strong>{{ $mataKuliahList->total() }}</strong></div>
            
        </div>

        <div class="table-responsive mk-table-wrapper">
            <table class="table modern-table align-middle text-start">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Prodi</th>
                        <th width="100">SKS</th>
                        <th width="80">Sem</th>
                        <th width="200">Pengampu</th>
                        <th width="100">Status</th>
                        <th width="250">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mataKuliahList as $mk)
                    <tr>
                        <td class="fw-medium">{{ ($mataKuliahList->firstItem() ?? 0) + $loop->index }}</td>
                        <td><strong>{{ $mk->kode_mk }}</strong></td>
                        <td>
                            {{ $mk->nama_mk }}
                            <br><small class="text-muted">{{ $mk->kurikulum }}</small>
                        </td>
                        <td><small>{{ $mk->prodi->nama_prodi ?? '-' }}</small></td>
                        <td class="text-center">{{ $mk->sks }}</td>
                        <td class="text-center">{{ $mk->semester ?? '-' }}</td>
                        <td>
                            @if($mk->pengampu->count() > 0)
                                @foreach($mk->pengampu->take(2) as $pengampu)
                                    <div class="small">
                                        {{ $pengampu->dosen->name ?? '-' }}
                                        @if($pengampu->dosen->no_induk)
                                            <small class="text-muted">({{ $pengampu->dosen->no_induk ?: $pengampu->dosen->username ?: '-' }})</small>
                                        @endif
                                    </div>
                                @endforeach
                                @if($mk->pengampu->count() > 2)
                                    <small class="text-muted">+{{ $mk->pengampu->count() - 2 }} lainnya</small>
                                @endif
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            {{ $mk->status ? 'Aktif' : 'Nonaktif' }}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm table-actions" role="group">
                                <a href="{{ route('mata-kuliah.show', $mk->id) }}" class="btn" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('mata-kuliah.edit', $mk->id) }}" class="btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('mata-kuliah.destroy', $mk->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" onclick="return confirmDelete(event, '{{ $mk->nama_mk }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">Belum ada Mata Kuliah</h5>
                            <p class="text-muted">Tambahkan mata kuliah baru atau impor dari CSV untuk memulai.</p>
                            <a href="{{ route('mata-kuliah.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Mata Kuliah
                            </a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-file-import me-2"></i>Import CSV
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $mataKuliahList->firstItem() }} - {{ $mataKuliahList->lastItem() }} dari {{ $mataKuliahList->total() }} data
                </div>
                <div>
                    {{ $mataKuliahList->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
            <!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Mata Kuliah dari CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Format: id_prodi, kurikulum, kode_mk, nama_mk, sks, semester_rekomendasi, status</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Jika kode sudah ada untuk prodi & kurikulum yang sama, data akan diupdate.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
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
            confirmButtonColor: '#0e4a95',
            confirmButtonText: 'OK'
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
});

// Delete confirmation function (outside DOMContentLoaded for global access)
function confirmDelete(event, mataKuliahName) {
    console.log('confirmDelete called with:', mataKuliahName);
    event.preventDefault();
    console.log('Form submission prevented');
    
    // Get the form from the button
    const form = event.target.closest('form');
    console.log('Form found:', form);
    
    if (typeof Swal === 'undefined') {
        console.log('SweetAlert not available, using fallback');
        // Fallback to native confirm
        if (confirm('Yakin hapus "' + mataKuliahName + '"?')) {
            console.log('Native confirm accepted, submitting form');
            form.submit();
        }
        return false;
    }
    
    console.log('Showing SweetAlert dialog');
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus mata kuliah "' + mataKuliahName + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        console.log('SweetAlert result:', result);
        if (result.isConfirmed) {
            console.log('User confirmed, submitting form');
            form.submit();
        } else {
            console.log('User cancelled or dismissed');
        }
    });
    
    return false;
}
</script>
@endpush

@endsection
