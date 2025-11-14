@extends('layouts.master')

@push('styles')
<style>
  /* Scoped to Program Studi index only */
  .prodi-table-wrapper .modern-table {
    border-collapse: separate;
    border-spacing: 0 8px; /* space between rows for card-like look */
  }
  .prodi-table-wrapper .modern-table thead th {
    background: #F8FAFC;
    color: #374151;
    font-weight: 600;
    border: none !important;
    padding: 12px 16px;
    text-transform: none;
  }
  .prodi-table-wrapper .modern-table tbody tr {
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  }
  .prodi-table-wrapper .modern-table tbody tr:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transform: translateY(-1px);
  }
  .prodi-table-wrapper .modern-table tbody td {
    border: none !important;
    padding: 14px 16px;
    vertical-align: middle;
  }
  .prodi-table-wrapper .modern-table tbody tr td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  .prodi-table-wrapper .modern-table tbody tr td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  .prodi-table-wrapper .table-actions .btn {
    border: 1px solid #E5E7EB;
    background: #ffffff;
    color: #374151;
  }
  .prodi-table-wrapper .table-actions .btn:hover {
    background: #F3F4F6;
  }
  .prodi-table-wrapper .card-header {
    background: #ffffff;
    border-bottom: 1px solid #E5E7EB;
    font-weight: 600;
  }
</style>
@endpush

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Program Studi</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <a href="{{ route('prodi.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Program Studi
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('prodi.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama program studi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenjang</label>
                    <select name="jenjang" class="form-select">
                        <option value="">Semua Jenjang</option>
                        <option value="D3" {{ request('jenjang') == 'D3' ? 'selected' : '' }}>D3</option>
                        <option value="D4" {{ request('jenjang') == 'D4' ? 'selected' : '' }}>D4</option>
                        <option value="S1" {{ request('jenjang') == 'S1' ? 'selected' : '' }}>S1</option>
                        <option value="S2" {{ request('jenjang') == 'S2' ? 'selected' : '' }}>S2</option>
                        <option value="S3" {{ request('jenjang') == 'S3' ? 'selected' : '' }}>S3</option>
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
                        <a href="{{ route('prodi.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- Summary under filters -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Program Studi: <strong>{{ $prodis->total() }}</strong></div>
        </div>

        <div class="table-responsive prodi-table-wrapper">
            <table class="table modern-table align-middle text-start">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode</th>
                        <th>Nama Program Studi</th>
                        <th>Jenjang</th>
                        <th>Akreditasi</th>
                        <th>Kaprodi</th>
                        <th>Mahasiswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prodis as $prodi)
                    <tr>
                        <td class="fw-medium">{{ ($prodis->firstItem() ?? 0) + $loop->index }}</td>
                        <td><strong>{{ $prodi->kode }}</strong></td>
                        <td>{{ $prodi->nama }}</td>
                        <td>{{ $prodi->jenjang }}</td>
                        <td>{{ $prodi->akreditasi ?? '-' }}</td>
                        <td>
                            @if($prodi->kaprodi)
                                {{ $prodi->kaprodi->name }}
                                <button type="button" class="btn btn-xs btn-link" onclick="showRotateModal({{ $prodi->id }}, '{{ $prodi->nama }}', '{{ $prodi->kaprodi->name }}')" title="Rotasi Kaprodi">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="showRotateModal({{ $prodi->id }}, '{{ $prodi->nama }}', null)" title="Set Kaprodi">
                                    Set Kaprodi
                                </button>
                            @endif
                        </td>
                        <td>{{ $prodi->mahasiswa->count() }}</td>
                        <td>{{ $prodi->kelas->count() }}</td>
                        <td>{{ $prodi->status == 1 ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm table-actions" role="group" aria-label="Aksi">
                                <a href="{{ route('prodi.show', $prodi->id) }}" class="btn" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('prodi.edit', $prodi->id) }}" class="btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn" onclick="confirmToggleStatus({{ $prodi->id }}, '{{ $prodi->nama }}', {{ $prodi->status }})" title="Toggle Status">
                                    <i class="fas fa-power-off"></i>
                                </button>
                                <button type="button" class="btn" onclick="confirmDeleteProdi({{ $prodi->id }}, '{{ $prodi->nama }}')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">Belum ada Program Studi</h5>
                            <p class="text-muted">Tambahkan program studi baru atau impor dari CSV untuk memulai.</p>
                            <a href="{{ route('prodi.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Program Studi
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
        
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $prodis->firstItem() ?? 0 }} - {{ $prodis->lastItem() ?? 0 }} dari {{ $prodis->total() }} data
            </div>
            {{ $prodis->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('prodi.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Program Studi dari CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Format: kode, nama, jenjang, akreditasi, email_kontak, telepon_kontak, kode_eksternal, status</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Jika kode sudah ada, data akan diupdate. Jika kode baru, data akan ditambahkan.
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
// Pass dosenList to JavaScript with safer encoding
const dosenList = @json($dosenList ?? []);

// Dynamic modal for rotate kaprodi
function showRotateModal(prodiId, prodiNama, currentKaprodi) {
    // Debug: Check if dosenList is available
    console.log('Opening modal for:', prodiId, prodiNama);
    console.log('Available dosen:', dosenList.length);
    console.log('Dosen data:', dosenList);
    
    // Generate dosen options
    let dosenOptions = '<option value="">-- Pilih Dosen --</option>';
    if (dosenList && dosenList.length > 0) {
        dosenList.forEach(dosen => {
            dosenOptions += `<option value="${dosen.id}">${dosen.name || dosen.nama || 'Unknown'}</option>`;
        });
    } else {
        dosenOptions += '<option value="">Tidak ada dosen tersedia</option>';
    }
    
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="rotateModal${prodiId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/prodi/${prodiId}/rotate-kaprodi" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Rotasi Kaprodi - ${prodiNama}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Pilih Kaprodi Baru</label>
                                <select name="kaprodi_user_id" class="form-select" required>
                                    ${dosenOptions}
                                </select>
                            </div>
                            ${currentKaprodi ? `
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Kaprodi saat ini: <strong>${currentKaprodi}</strong>
                                </div>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById(`rotateModal${prodiId}`);
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById(`rotateModal${prodiId}`));
    modal.show();
    
    // Remove modal from DOM after hidden
    document.getElementById(`rotateModal${prodiId}`).addEventListener('hidden.bs.modal', function () {
        this.remove();
    });
}

function confirmDeleteProdi(id, nama) {
    Swal.fire({
        title: 'Hapus Program Studi?',
        text: 'Yakin ingin menghapus program studi "' + nama + '"? Tindakan ini akan menghapus semua data terkait termasuk mahasiswa dan kelas dalam program studi ini.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/prodi/' + id;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmToggleStatus(id, nama, currentStatus) {
    const action = currentStatus ? 'menonaktifkan' : 'mengaktifkan';
    const actionText = currentStatus ? 'Nonaktifkan' : 'Aktifkan';
    
    Swal.fire({
        title: actionText + ' Program Studi?',
        text: 'Yakin ingin ' + action + ' program studi "' + nama + '"?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: actionText,
        cancelButtonText: 'Batal',
        confirmButtonColor: currentStatus ? '#ffc107' : '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/prodi/' + id + '/toggle-status';
        }
    });
}
</script>
@endpush

@endsection
