@extends('layouts.master')

@section('title', 'Pengaturan Semester')

@push('styles')
<style>
  .page-header {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  
  .semester-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
  }
  
  .semester-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  
  .semester-card.active {
    border: 2px solid #10b981;
  }
  
  .badge-active {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  
  .info-box {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1 fw-bold">
        <i class="fas fa-calendar-alt me-2 text-primary"></i>
        Pengaturan Semester
      </h4>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">
        <i class="fas fa-info-circle me-1"></i>
        Atur jumlah pertemuan, UTS, dan UAS untuk setiap semester
      </p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSemesterModal">
      <i class="fas fa-plus me-2"></i>
      Tambah Semester
    </button>
  </div>
</div>

<!-- Info Box -->
<div class="info-box">
  <div class="d-flex align-items-start">
    <i class="fas fa-lightbulb fa-2x text-warning me-3"></i>
    <div>
      <h6 class="fw-bold mb-2">Panduan Pengaturan</h6>
      <ul class="mb-0 small text-muted">
        <li><strong>Jumlah Pertemuan:</strong> Total pertemuan dalam 1 semester (biasanya 14-16 pertemuan)</li>
        <li><strong>Pertemuan UTS:</strong> Pertemuan ke berapa UTS dilaksanakan (misal: pertemuan ke-8)</li>
        <li><strong>Pertemuan UAS:</strong> Pertemuan ke berapa UAS dilaksanakan (misal: pertemuan ke-16)</li>
      </ul>
    </div>
  </div>
</div>

<!-- Semester List -->
<div class="row">
  @forelse($semesters as $semester)
  <div class="col-md-6 mb-4">
    <div class="card semester-card {{ $semester->status == 'aktif' ? 'active' : '' }}">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="fw-bold mb-1">{{ $semester->tahun_ajaran }}</h5>
            <p class="text-muted mb-0">Semester {{ $semester->semester == 1 ? 'Ganjil' : 'Genap' }}</p>
          </div>
          @if($semester->status == 'aktif')
          <span class="badge badge-active px-3 py-2">
            <i class="fas fa-check-circle me-1"></i>
            Aktif
          </span>
          @else
          <span class="badge bg-secondary px-3 py-2">Tidak Aktif</span>
          @endif
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-check text-primary"></i>
              </div>
              <div>
                <small class="text-muted d-block">Total Pertemuan</small>
                <strong class="fs-5">{{ $semester->jumlah_pertemuan ?? 16 }}</strong>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clipboard-list text-warning"></i>
              </div>
              <div>
                <small class="text-muted d-block">UTS</small>
                <strong class="fs-5">{{ $semester->pertemuan_uts ? 'Pertemuan ' . $semester->pertemuan_uts : '-' }}</strong>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-graduation-cap text-danger"></i>
              </div>
              <div>
                <small class="text-muted d-block">UAS</small>
                <strong class="fs-5">{{ $semester->pertemuan_uas ? 'Pertemuan ' . $semester->pertemuan_uas : '-' }}</strong>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar text-info"></i>
              </div>
              <div>
                <small class="text-muted d-block">Periode</small>
                <strong class="small">{{ $semester->tanggal_mulai ? $semester->tanggal_mulai->format('d/m/Y') : '-' }}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-primary flex-fill" onclick="editSemester({{ $semester->id }})">
            <i class="fas fa-edit me-1"></i>
            Edit
          </button>
          @if($semester->status != 'aktif')
          <form action="{{ route('admin.semester.activate', $semester->id) }}" method="POST" class="flex-fill">
            @csrf
            <button type="submit" class="btn btn-sm btn-success w-100">
              <i class="fas fa-check me-1"></i>
              Aktifkan
            </button>
          </form>
          @endif
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="card semester-card">
      <div class="card-body text-center py-5">
        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">Belum Ada Semester</h5>
        <p class="text-muted mb-0">Klik tombol "Tambah Semester" untuk membuat semester baru</p>
      </div>
    </div>
  </div>
  @endforelse
</div>

<!-- Add/Edit Semester Modal -->
<div class="modal fade" id="addSemesterModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-calendar-plus me-2"></i>
          <span id="modalTitle">Tambah Semester</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="semesterForm" method="POST" action="{{ route('admin.semester.store') }}">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="semester_id" id="semesterId">
        
        <div class="modal-body">
          <div class="mb-3">
            <label for="tahun_ajaran" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" placeholder="2024/2025" required>
          </div>

          <div class="mb-3">
            <label for="semester" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
            <select class="form-select" id="semester" name="semester" required>
              <option value="">Pilih Semester</option>
              <option value="1">Ganjil</option>
              <option value="2">Genap</option>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
            </div>
            <div class="col-md-6 mb-3">
              <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
              <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
            </div>
          </div>

          <hr>

          <div class="mb-3">
            <label for="jumlah_pertemuan" class="form-label fw-semibold">
              <i class="fas fa-calendar-check me-1 text-primary"></i>
              Jumlah Pertemuan <span class="text-danger">*</span>
            </label>
            <input type="number" class="form-control" id="jumlah_pertemuan" name="jumlah_pertemuan" value="16" min="1" max="20" required>
            <small class="text-muted">Total pertemuan dalam 1 semester (biasanya 14-16)</small>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="pertemuan_uts" class="form-label fw-semibold">
                <i class="fas fa-clipboard-list me-1 text-warning"></i>
                Pertemuan UTS
              </label>
              <input type="number" class="form-control" id="pertemuan_uts" name="pertemuan_uts" min="1" max="20" placeholder="8">
              <small class="text-muted">Pertemuan ke berapa UTS</small>
            </div>
            <div class="col-md-6 mb-3">
              <label for="pertemuan_uas" class="form-label fw-semibold">
                <i class="fas fa-graduation-cap me-1 text-danger"></i>
                Pertemuan UAS
              </label>
              <input type="number" class="form-control" id="pertemuan_uas" name="pertemuan_uas" min="1" max="20" placeholder="16">
              <small class="text-muted">Pertemuan ke berapa UAS</small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function editSemester(id) {
  // Fetch semester data
  fetch(`/admin/semester/${id}/edit`)
    .then(response => response.json())
    .then(data => {
      document.getElementById('modalTitle').textContent = 'Edit Semester';
      document.getElementById('formMethod').value = 'PUT';
      document.getElementById('semesterId').value = data.id;
      document.getElementById('semesterForm').action = `/admin/semester/${data.id}`;
      
      document.getElementById('tahun_ajaran').value = data.tahun_ajaran;
      document.getElementById('semester').value = data.semester;
      document.getElementById('tanggal_mulai').value = data.tanggal_mulai;
      document.getElementById('tanggal_selesai').value = data.tanggal_selesai;
      document.getElementById('jumlah_pertemuan').value = data.jumlah_pertemuan || 16;
      document.getElementById('pertemuan_uts').value = data.pertemuan_uts || '';
      document.getElementById('pertemuan_uas').value = data.pertemuan_uas || '';
      
      new bootstrap.Modal(document.getElementById('addSemesterModal')).show();
    });
}

// Reset form when modal is closed
document.getElementById('addSemesterModal').addEventListener('hidden.bs.modal', function () {
  document.getElementById('semesterForm').reset();
  document.getElementById('modalTitle').textContent = 'Tambah Semester';
  document.getElementById('formMethod').value = 'POST';
  document.getElementById('semesterForm').action = '{{ route("admin.semester.store") }}';
  document.getElementById('semesterId').value = '';
});
</script>
@endpush
