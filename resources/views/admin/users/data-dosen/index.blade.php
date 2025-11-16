@extends('layouts.master')

@section('title', 'Data Dosen')
@section('page-title', 'Data Dosen')

@push('styles')
<style>
  /* Scoped to Dosen index only */
  .dosen-table-wrapper .modern-table { border-collapse: separate; border-spacing: 0 8px; }
  .dosen-table-wrapper thead th { background:#F8FAFC; color:#374151; font-weight:600; border:none !important; padding:12px 16px; text-transform:none; letter-spacing: normal; }
  .dosen-table-wrapper tbody tr { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); }
  .dosen-table-wrapper tbody tr:hover { box-shadow:0 4px 10px rgba(0,0,0,.08); transform: translateY(-1px); }
  .dosen-table-wrapper tbody td { border:none !important; padding:14px 16px; vertical-align: middle; }
  .dosen-table-wrapper tbody tr td:first-child { border-top-left-radius:12px; border-bottom-left-radius:12px; }
  .dosen-table-wrapper tbody tr td:last-child { border-top-right-radius:12px; border-bottom-right-radius:12px; }
  .dosen-table-wrapper .table-actions .btn { border:1px solid #E5E7EB; background:#fff; color:#374151; }
  .dosen-table-wrapper .table-actions .btn:hover { background:#F3F4F6; }
  .dosen-table-wrapper .card-header { background:#fff; border-bottom:1px solid #E5E7EB; font-weight:600; }
</style>
@endpush

@section('content')


<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Dosen</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importDosenModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Dosen
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('admin.dosen.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari NIDN, nama, atau email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jabatan Akademik</label>
                    <select name="jabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                        @foreach($jabatanList as $jabatan)
                            <option value="{{ $jabatan }}" {{ request('jabatan') == $jabatan ? 'selected' : '' }}>{{ $jabatan }}</option>
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
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- Summary under filters -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Dosen: <strong>{{ $dosens->total() }}</strong></div>
            
        </div>
        <div class="table-responsive dosen-table-wrapper">
        <table class="table modern-table align-middle">
          <thead>
            <tr>
              <th style="width: 60px;">No</th>
              <th>NIDN</th>
              <th>Nama Dosen</th>
              <th>Jabatan</th>
              <th>Status</th>
              <th width="250">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dosens as $dosen)
              <tr>
                <td class="fw-medium">{{ ($dosens->firstItem() ?? 0) + $loop->index }}</td>
                <td><strong>{{ $dosen->nidn }}</strong></td>
                <td>{{ $dosen->nama }}</td>
                <td>{{ $dosen->jabatan_akademik }}</td>
                <td>{{ $dosen->status_aktif == 1 ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                  <div class="btn-group btn-group-sm table-actions" role="group">
                    <a href="{{ route('admin.dosen.show', $dosen->id) }}" class="btn" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="btn" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.dosen.archive', $dosen->id) }}" method="POST" style="display:inline;" id="archive-form-{{ $dosen->id }}">
                      @csrf
                      <button type="button" class="btn" onclick="confirmArchive('archive-form-{{ $dosen->id }}')" title="Arsip">
                        <i class="fas fa-archive"></i>
                      </button>
                    </form>
                    <button type="button" class="btn" onclick="confirmDelete({{ $dosen->id }}, '{{ $dosen->nama }}')" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted">Tidak ada data</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $dosens->firstItem() }} - {{ $dosens->lastItem() }} dari {{ $dosens->total() }} data
                </div>
                <div>
                    {{ $dosens->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

  <!-- Import Dosen Modal -->
  <div class="modal fade" id="importDosenModal" tabindex="-1" aria-labelledby="importDosenModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/dosen/import" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="importDosenModalLabel">Import Data Dosen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">File CSV/Excel</label>
              <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
              <small class="text-muted">Format kolom minimal: nidn, nama, email, jabatan_akademik</small>
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

  <!-- Modal Tampilkan Password Baru -->
  @if(session('new_password'))
  <div class="modal fade" id="newPasswordModal" tabindex="-1" aria-labelledby="newPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 bg-success text-white">
          <h5 class="modal-title fw-semibold" id="newPasswordModalLabel">
            <i class="fas fa-check-circle me-2"></i>
            Password Berhasil Direset
          </h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Penting!</strong> Salin password ini sekarang. Password tidak akan ditampilkan lagi.
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Dosen:</label>
            <div class="p-2 bg-light rounded">{{ session('reset_user_name') }}</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">NIDN:</label>
            <div class="p-2 bg-light rounded">{{ session('reset_user_nidn') }}</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Email:</label>
            <div class="p-2 bg-light rounded">{{ session('reset_user_email') }}</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Password Baru:</label>
            <div class="input-group">
              <input type="text" class="form-control form-control-lg fw-bold text-center" id="newPasswordText" value="{{ session('new_password') }}" readonly style="font-size: 1.25rem; letter-spacing: 2px; background: #FEF3C7; color: #92400E; border: 2px solid #FCD34D;">
              <button class="btn btn-warning" type="button" onclick="copyPassword()" title="Salin Password">
                <i class="fas fa-copy"></i>
              </button>
            </div>
            <small class="text-muted">Klik tombol untuk menyalin password</small>
          </div>
          
          <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Catatan:</strong>
            <ul class="mb-0 mt-2 ps-3">
              <li>Berikan password ini kepada dosen yang bersangkutan</li>
              <li>Dosen harus mengganti password saat login pertama</li>
              <li>Simpan password ini dengan aman</li>
            </ul>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">
            <i class="fas fa-check me-2"></i>
            Saya Sudah Menyalin Password
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif
@endsection

@push('scripts')
  <script>
    // Auto-show modal jika ada password baru (Bootstrap 5)
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

      @if(session('new_password'))
        var newPasswordModal = new bootstrap.Modal(document.getElementById('newPasswordModal'));
        newPasswordModal.show();
      @endif
    });

    // Copy password helper
    function copyPassword() {
      var passwordText = document.getElementById('newPasswordText');
      passwordText.select();
      passwordText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(passwordText.value).then(function() {
        Swal.fire({
          icon: 'success',
          title: 'Tersalin!',
          text: 'Password telah disalin ke clipboard.',
          timer: 1500,
          showConfirmButton: false
        });
      });
    }

    // Delete confirmation
    function confirmDelete(id, nama) {
      Swal.fire({
        title: 'Hapus Dosen?',
        text: 'Yakin ingin menghapus dosen "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
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
          form.action = '{{ route("admin.dosen.destroy", ":id") }}'.replace(':id', id);
          
          var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          var csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = csrfToken;
          
          var methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'DELETE';
          
          form.appendChild(csrfInput);
          form.appendChild(methodInput);
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmArchive(formId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Arsip Dosen?',
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
        if (confirm('Arsip dosen? Data akan dipindahkan ke arsip dan dapat dikembalikan kembali.')) {
          document.getElementById(formId).submit();
        }
      }
    }
  </script>
@endpush
