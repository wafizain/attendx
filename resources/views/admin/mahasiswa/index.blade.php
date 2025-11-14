@extends('layouts.master')

@push('styles')
<style>
  /* Card */
  .data-card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.05); border:0; overflow:hidden; }
  .data-card .card-body { padding:1rem 1.25rem; }

  /* Modern table */
  .mahasiswa-table-wrapper .modern-table { border-collapse:separate; border-spacing:0 8px; }
  .mahasiswa-table-wrapper thead th { background:#F8FAFC; color:#374151; font-weight:600; border:none !important; padding:12px 16px; }
  .mahasiswa-table-wrapper tbody tr { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); }
  .mahasiswa-table-wrapper tbody tr:hover { box-shadow:0 4px 10px rgba(0,0,0,.08); transform:translateY(-1px); }
  .mahasiswa-table-wrapper tbody td { border:none !important; padding:14px 16px; vertical-align:middle; }
  .mahasiswa-table-wrapper tbody tr td:first-child { border-top-left-radius:12px; border-bottom-left-radius:12px; }
  .mahasiswa-table-wrapper tbody tr td:last-child { border-top-right-radius:12px; border-bottom-right-radius:12px; }

  /* Actions */
  .mahasiswa-table-wrapper .table-actions .btn { border:1px solid #E5E7EB; background:#fff; color:#374151; }
  .mahasiswa-table-wrapper .table-actions .btn:hover { background:#F3F4F6; }
</style>
@endpush

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Mahasiswa</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Mahasiswa
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('mahasiswa.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari NIM, nama, atau email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Program Studi</label>
                    <select name="prodi" class="form-select" id="filterProdi">
                        <option value="">Semua Prodi</option>
                        @foreach($prodiList as $prodi)
                            <option value="{{ $prodi->id }}" {{ request('prodi') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Angkatan</label>
                    <select name="angkatan" class="form-select">
                        <option value="">Semua Angkatan</option>
                        @foreach($angkatanList as $angkatan)
                            <option value="{{ $angkatan }}" {{ request('angkatan') == $angkatan ? 'selected' : '' }}>{{ $angkatan }}</option>
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
                        <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- Summary under filters -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Mahasiswa: <strong>{{ $statistik['total'] }}</strong></div>
            
        </div>
        <div class="table-responsive mahasiswa-table-wrapper">
            <table class="table modern-table align-middle text-start">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prodi</th>
                        <th>Angkatan</th>
                        <th>Kelas</th>
                        <th>Biometrik</th>
                        <th width="250">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswaList as $mhs)
                    <tr>
                        <td class="fw-medium">{{ ($mahasiswaList->firstItem() ?? 0) + $loop->index }}</td>
                        <td class="text-start"><strong><code class="text-dark">{{ $mhs->nim }}</code></strong></td>
                        <td class="text-start">
                            <a href="{{ route('mahasiswa.edit', $mhs->id) }}" class="text-decoration-none text-dark">
                                {{ $mhs->nama }}
                            </a>
                        </td>
                        <td class="text-start"><small>{{ $mhs->prodi->nama ?? '-' }}</small></td>
                        <td class="text-start">{{ $mhs->angkatan }}</td>
                        <td class="text-start">
                            <small>
                                {{ $mhs->kelas->nama
                                    ?? optional(optional($mhs->kelasMembers->firstWhere('tanggal_keluar', null))->kelas)->nama
                                    ?? '-' }}
                            </small>
                        </td>
                        <td class="text-start">
                            @if($mhs->fp_enrolled)
                                <i class="fas fa-check-circle text-success" title="Sudah Enrol"></i>
                            @else
                                <i class="fas fa-times-circle text-danger" title="Belum Enrol"></i>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm table-actions" role="group">
                                <a href="{{ route('mahasiswa.show', $mhs->id) }}" class="btn" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('mahasiswa.edit', $mhs->id) }}" class="btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('mahasiswa.archive', $mhs->id) }}" method="POST" style="display:inline;" id="archive-form-{{ $mhs->id }}">
                                    @csrf
                                    <button type="button" class="btn" onclick="confirmArchive('archive-form-{{ $mhs->id }}')" title="Arsip">
                                        <i class="fas fa-archive"></i>
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
    <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $mahasiswaList->firstItem() }} - {{ $mahasiswaList->lastItem() }} dari {{ $mahasiswaList->total() }} data
                </div>
                <div>
                    {{ $mahasiswaList->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File CSV/Excel</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format:</strong> nim, nama, email, no_hp, id_prodi, angkatan, status_akademik, create_account
                        <br>
                        <a href="{{ route('mahasiswa.download-template') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-download"></i> Download Template
                        </a>
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
// SweetAlert flash messages after redirect (create/update/delete)
document.addEventListener('DOMContentLoaded', function() {
  const flashSuccess = @json(session('success'));
  const flashError = @json(session('error'));
  if (typeof Swal !== 'undefined') {
    if (flashSuccess) {
      Swal.fire({ icon: 'success', title: 'Berhasil', text: flashSuccess, confirmButtonColor: '#0e4a95' });
    } else if (flashError) {
      Swal.fire({ icon: 'error', title: 'Gagal', text: flashError, confirmButtonColor: '#0e4a95' });
    }
  }
});

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

function confirmArchive(formId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Arsip Mahasiswa?',
            text: 'Data akan dipindahkan ke arsip dan dapat dikembalikan kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Arsipkan',
            confirmButtonColor: '#0e4a95',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    } else {
        if (confirm('Arsip mahasiswa? Data akan dipindahkan ke arsip dan dapat dikembalikan kembali.')) {
            document.getElementById(formId).submit();
        }
    }
}

// Removed bulk selection and reset password actions from index per request

// Toggle status function
function toggleStatus(id) {
    if (!confirm('Toggle status akademik mahasiswa ini?')) {
        return;
    }
    
    fetch(`/mahasiswa/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error);
    });
}

// Dependent dropdown: Kelas berdasarkan Prodi + Angkatan
document.getElementById('filterProdi')?.addEventListener('change', updateKelasDropdown);
document.getElementById('filterAngkatan')?.addEventListener('change', updateKelasDropdown);

function updateKelasDropdown() {
    const prodiId = document.getElementById('filterProdi').value;
    const angkatan = document.getElementById('filterAngkatan').value;
    const kelasSelect = document.getElementById('filterKelas');
    
    if (!prodiId || !angkatan) {
        return;
    }
    
    // Fetch kelas berdasarkan prodi dan angkatan
    fetch(`/api/kelas?prodi=${prodiId}&angkatan=${angkatan}`)
        .then(response => response.json())
        .then(data => {
            kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
            data.forEach(kelas => {
                kelasSelect.innerHTML += `<option value="${kelas.id}">${kelas.kode} - ${kelas.nama}</option>`;
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>
@endpush
@endsection
