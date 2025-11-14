@extends('layouts.master')

@section('title', 'Data Kelas')
@section('page-title', 'Data Kelas')

@push('styles')
  <!-- DataTables Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  
  <style>
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
    
    .filter-card {
      background: #F9FAFB;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    
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
    
    .badge-modern {
      padding: 0.375rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: 6px;
    }
    
    .btn-action {
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
      border-radius: 6px;
      transition: all 0.2s ease;
    }
    
    .btn-action:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

  <!-- Filter Card -->
  <div class="filter-card">
    <form method="GET" action="{{ route('kelas.index') }}" class="row g-3">
      <div class="col-md-3">
        <label class="form-label fw-semibold" style="font-size: 0.875rem;">Mata Kuliah</label>
        <select name="mata_kuliah_id" class="form-select form-select-sm">
          <option value="">Semua Mata Kuliah</option>
          @foreach($mataKuliahList as $mk)
            <option value="{{ $mk->id }}" {{ request('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
              {{ $mk->kode_mk }} - {{ $mk->nama_mk }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold" style="font-size: 0.875rem;">Tahun Ajaran</label>
        <select name="tahun_ajaran" class="form-select form-select-sm">
          <option value="">Semua Tahun</option>
          @foreach($tahunAjaranList as $tahun)
            <option value="{{ $tahun }}" {{ request('tahun_ajaran') == $tahun ? 'selected' : '' }}>
              {{ $tahun }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold" style="font-size: 0.875rem;">Semester</label>
        <select name="semester" class="form-select form-select-sm">
          <option value="">Semua Semester</option>
          <option value="ganjil" {{ request('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
          <option value="genap" {{ request('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold" style="font-size: 0.875rem;">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Semua Status</option>
          <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
          <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fas fa-filter me-1"></i> Filter
        </button>
        <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-redo me-1"></i> Reset
        </a>
      </div>
    </form>
  </div>

  <!-- Data Card -->
  <div class="data-card">
    <div class="data-card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-1 fw-semibold">Daftar Kelas</h5>
        <p class="text-muted mb-0" style="font-size: 0.875rem;">Kelola data kelas perkuliahan</p>
      </div>
      <a href="{{ route('kelas.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>
        Tambah Kelas
      </a>
    </div>
    
    <div class="table-responsive">
      <table class="table table-modern" id="kelasTable">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode MK</th>
            <th>Mata Kuliah</th>
            <th>Nama Kelas</th>
            <th>Dosen Pengampu</th>
            <th>Tahun Ajaran</th>
            <th>Semester</th>
            <th>Mahasiswa</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($kelas as $item)
            <tr>
              <td class="fw-medium">{{ $loop->iteration }}</td>
              <td>
                <span class="badge bg-primary">{{ $item->kode_mk }}</span>
              </td>
              <td>
                <div class="fw-semibold">{{ $item->nama_mk }}</div>
                <small class="text-muted">{{ $item->sks }} SKS</small>
              </td>
              <td>
                <span class="badge bg-info" style="font-size: 0.875rem;">{{ $item->nama_kelas }}</span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="d-flex align-items-center justify-content-center rounded-circle bg-gradient" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3B82F6, #60A5FA);">
                    <i class="fas fa-chalkboard-teacher text-white" style="font-size: 0.75rem;"></i>
                  </div>
                  <div>
                    <div class="fw-semibold" style="font-size: 0.875rem;">{{ $item->dosen_name }}</div>
                    @if($item->nidn)
                      <small class="text-muted">NIDN: {{ $item->nidn }}</small>
                    @endif
                  </div>
                </div>
              </td>
              <td>{{ $item->tahun_ajaran }}</td>
              <td>
                <span class="badge {{ $item->semester == 'ganjil' ? 'bg-warning' : 'bg-success' }}">
                  {{ ucfirst($item->semester) }}
                </span>
              </td>
              <td>
                <span class="badge bg-secondary">
                  {{ $item->jumlah_mahasiswa }}/{{ $item->kapasitas }}
                </span>
              </td>
              <td>
                @if($item->status == 'aktif')
                  <span class="badge-modern bg-success text-white">Aktif</span>
                @else
                  <span class="badge-modern bg-secondary text-white">Nonaktif</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('kelas.show', $item->id) }}" class="btn btn-info btn-action" title="Detail">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('kelas.mahasiswa', $item->id) }}" class="btn btn-primary btn-action" title="Kelola Mahasiswa">
                    <i class="fas fa-users"></i>
                  </a>
                  <a href="{{ route('kelas.jadwal', $item->id) }}" class="btn btn-secondary btn-action" title="Kelola Jadwal">
                    <i class="fas fa-calendar-alt"></i>
                  </a>
                  <a href="{{ route('kelas.edit', $item->id) }}" class="btn btn-warning btn-action" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button type="button" class="btn btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
                
                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header border-0">
                        <h5 class="modal-title fw-semibold" id="deleteModalLabel{{ $item->id }}">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-2">Yakin ingin menghapus kelas <strong>{{ $item->nama_kelas }}</strong>?</p>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">
                          Mata Kuliah: {{ $item->nama_mk }}<br>
                          Dosen: {{ $item->dosen_name }}
                        </p>
                        <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.875rem;">
                          <i class="fas fa-exclamation-triangle me-2"></i>
                          Kelas tidak dapat dihapus jika masih memiliki mahasiswa atau sesi absensi.
                        </div>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form action="{{ route('kelas.destroy', $item->id) }}" method="POST" style="display:inline-block;">
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
              <td colspan="10" class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                Belum ada data kelas
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    @if($kelas->hasPages())
      <div class="p-3">
        {{ $kelas->links() }}
      </div>
    @endif
  </div>
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
      $('#kelasTable').DataTable({
        responsive: true,
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
        order: [[5, 'desc'], [6, 'desc']], // Sort by tahun ajaran & semester
        columnDefs: [
          { orderable: false, targets: [9] } // Disable sorting on action column
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
        }
      });
    });
  </script>
@endpush
