@extends('layouts.master')

@section('title', 'Data Admin')
@section('page-title', 'Data Admin')

@push('styles')
  <!-- DataTables Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  
  <style>
    /* Modern Card */
    .data-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      border: 1px solid #E5E7EB;
      overflow: hidden;
    }
    
    .data-card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #E5E7EB;
      background: white;
    }
    
    .data-card-body {
      padding: 0;
    }
    
    /* Table Styling */
    .table-modern {
      margin-bottom: 0;
    }
    
    .table-modern thead th {
      background: #F9FAFB;
      color: #111827;
      font-weight: 600;
      font-size: 0.8125rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #E5E7EB;
    }
    
    .table-modern tbody td {
      padding: 1rem 1.5rem;
      vertical-align: middle;
      color: #374151;
      font-size: 0.875rem;
    }
    
    .table-modern tbody tr {
      border-bottom: 1px solid #F3F4F6;
      transition: background-color 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
      background: #F9FAFB;
    }
    
    .table-modern tbody tr:last-child {
      border-bottom: none;
    }
    
    /* Badge Styling */
    .badge-modern {
      padding: 0.375rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: 6px;
      letter-spacing: 0.025em;
    }
    
    .badge-role-admin {
      background: #FEE2E2;
      color: #991B1B;
    }
    
    .badge-role-dosen {
      background: #DBEAFE;
      color: #1E40AF;
    }
    
    .badge-role-mahasiswa {
      background: #E5E7EB;
      color: #374151;
    }
    
    .badge-status-active {
      background: #D1FAE5;
      color: #065F46;
    }
    
    .badge-status-inactive {
      background: #F3F4F6;
      color: #6B7280;
    }
    
    /* Button Actions */
    .btn-action {
      padding: 0.375rem 0.75rem;
      font-size: 0.8125rem;
      border-radius: 6px;
      transition: all 0.2s ease;
    }
    
    .btn-action i {
      font-size: 0.875rem;
    }
    
    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
      border-radius: 8px;
      border: 1px solid #E5E7EB;
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
    }
    
    .dataTables_wrapper .dataTables_length select:focus,
    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: #10B981;
      outline: none;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    
    .dataTables_wrapper .dataTables_info {
      color: #6B7280;
      font-size: 0.875rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border-radius: 6px;
      padding: 0.5rem 0.75rem;
      margin: 0 0.125rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: #10B981 !important;
      border-color: #10B981 !important;
      color: white !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #F3F4F6 !important;
      border-color: #E5E7EB !important;
      color: #111827 !important;
    }
  </style>
@endpush

@section('content')
  <!-- Alert Success -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Alert Error -->
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Data Card -->
  <div class="data-card">
    <div class="data-card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-1 fw-semibold">Daftar Admin</h5>
        <p class="text-muted mb-0" style="font-size: 0.875rem;">Kelola data administrator sistem</p>
      </div>
      <a href="{{ route('admin.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>
        Tambah Admin
      </a>
    </div>

    <div class="data-card-body">
      <div class="table-responsive">
        <table id="adminTable" class="table table-modern">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $user)
              @php
                $role = strtolower($user->role);
                $roleClass = $role === 'admin' ? 'badge-role-admin' : ($role === 'dosen' ? 'badge-role-dosen' : 'badge-role-mahasiswa');
              @endphp
              <tr>
                <td class="fw-medium">{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-gradient" style="width: 36px; height: 36px; background: linear-gradient(135deg, #FF6B6B, #FF8E8E);">
                      <i class="fas fa-user text-white" style="font-size: 0.875rem;"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">{{ $user->name }}</div>
                    </div>
                  </div>
                </td>
                <td class="text-muted">{{ $user->email }}</td>
                <td>
                  <span class="badge-modern {{ $roleClass }} text-uppercase">{{ $user->role }}</span>
                </td>
                <td>
                  @if(isset($user->status) && $user->status == 'nonaktif')
                    <span class="badge-modern badge-status-inactive">Nonaktif</span>
                  @else
                    <span class="badge-modern badge-status-active">Aktif</span>
                  @endif
                </td>
                <td class="text-muted">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('admin.edit', $user->id) }}" class="btn btn-warning btn-action" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($user->id != auth()->id())
                      <button type="button" class="btn btn-info btn-action" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}" title="Reset Password">
                        <i class="fas fa-key"></i>
                      </button>
                    @endif
                    <button type="button" class="btn btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                  
                  <!-- Reset Password Modal -->
                  @if($user->id != auth()->id())
                  <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" aria-labelledby="resetPasswordModalLabel{{ $user->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header border-0">
                          <h5 class="modal-title fw-semibold" id="resetPasswordModalLabel{{ $user->id }}">
                            <i class="fas fa-key text-info me-2"></i>
                            Konfirmasi Reset Password
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p class="mb-3">Yakin ingin mereset password untuk admin <strong>{{ $user->name }}</strong>?</p>
                          <div class="alert alert-warning mb-0" style="font-size: 0.875rem;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                              <li>Password baru akan di-generate otomatis</li>
                              <li>Admin harus mengganti password saat login pertama</li>
                              <li>Password lama tidak akan bisa digunakan lagi</li>
                            </ul>
                          </div>
                        </div>
                        <div class="modal-footer border-0">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                          <form action="{{ route('admin.reset-password', $user->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-info">
                              <i class="fas fa-key me-2"></i>
                              Reset Password
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  @endif
                  
                  <!-- Delete Modal -->
                  <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header border-0">
                          <h5 class="modal-title fw-semibold" id="deleteModalLabel{{ $user->id }}">Konfirmasi Hapus</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p class="mb-0">Yakin ingin menghapus admin <strong>{{ $user->name }}</strong>?</p>
                          <p class="text-muted mb-0" style="font-size: 0.875rem;">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                        <div class="modal-footer border-0">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                          <form action="{{ route('admin.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                  Belum ada data admin
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
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
            <label class="form-label fw-semibold">Admin:</label>
            <div class="p-2 bg-light rounded">{{ session('reset_user_name') }}</div>
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
              <li>Berikan password ini kepada admin yang bersangkutan</li>
              <li>Admin harus mengganti password saat login pertama</li>
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
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <!-- DataTables Bootstrap 5 JS -->
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function() {
      $('#adminTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'asc']],
        columnDefs: [
          { orderable: false, targets: [6] } // Disable sorting on action column
        ],
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ data",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
          infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
          infoFiltered: "(disaring dari _MAX_ total data)",
          zeroRecords: "Data tidak ditemukan",
          emptyTable: "Tidak ada data yang tersedia",
          paginate: {
            first: "Pertama",
            last: "Terakhir",
            next: "Selanjutnya",
            previous: "Sebelumnya"
          }
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
      });

      // Auto-show modal jika ada password baru
      @if(session('new_password'))
        var newPasswordModal = new bootstrap.Modal(document.getElementById('newPasswordModal'));
        newPasswordModal.show();
      @endif
    });

    // Function untuk copy password
    function copyPassword() {
      var passwordText = document.getElementById('newPasswordText');
      passwordText.select();
      passwordText.setSelectionRange(0, 99999); // For mobile devices
      
      // Copy to clipboard
      navigator.clipboard.writeText(passwordText.value).then(function() {
        // Show success feedback
        var copyBtn = event.target.closest('button');
        var originalHTML = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
        copyBtn.classList.remove('btn-warning');
        copyBtn.classList.add('btn-success');
        
        setTimeout(function() {
          copyBtn.innerHTML = originalHTML;
          copyBtn.classList.remove('btn-success');
          copyBtn.classList.add('btn-warning');
        }, 2000);
      }).catch(function(err) {
        alert('Gagal menyalin password. Silakan salin manual.');
      });
    }
  </script>
@endpush
