@extends('layouts.master')

@section('title','Laporan & Rekap Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
<style>
  .report-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
  }
  
  .report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  
  .report-card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
  }
  
  .report-card-header.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  
  .report-card-header.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  }
  
  .report-card-header.info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  }
  
  .report-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  
  .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
  }
  
  .form-control, .form-select {
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    transition: all 0.2s;
  }
  
  .form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
  
  .btn-generate {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s;
  }
  
  .info-card {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  
  .feature-list {
    list-style: none;
    padding: 0;
  }
  
  .feature-list li {
    padding: 0.5rem 0;
    padding-left: 1.75rem;
    position: relative;
    color: #6b7280;
    font-size: 0.875rem;
  }
  
  .feature-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
  }
  
  .page-header {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1 fw-bold">
        <i class="fas fa-chart-bar me-2 text-primary"></i>
        Laporan & Rekap Absensi
      </h4>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">
        <i class="fas fa-info-circle me-1"></i>
        Pilih jenis laporan yang ingin Anda generate
      </p>
    </div>
  </div>
</div>

<div class="row mb-4">
  <!-- Laporan Per Kelas -->
  <div class="col-lg-6 mb-4">
    <div class="card report-card">
      <div class="card-header report-card-header">
        <div class="d-flex align-items-center">
          <div class="report-icon me-3">
            <i class="fas fa-users text-white"></i>
          </div>
          <div class="text-white">
            <h5 class="mb-0 fw-bold">Laporan Per Kelas</h5>
            <small class="opacity-75">Rekap absensi berdasarkan kelas</small>
          </div>
        </div>
      </div>
      <form action="{{ route('reports.by-class') }}" method="GET">
        <div class="card-body p-4">
          <div class="mb-3">
                <label for="kelas_id" class="form-label">Pilih Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" id="kelas_id" class="form-control select2" required>
                  <option value="">-- Pilih Kelas --</option>
                  @foreach($kelas as $k)
                    <option value="{{ $k->id }}">
                      @if($k->mataKuliah)
                        {{ $k->mataKuliah->nama_mk }} - {{ $k->nama_kelas ?? $k->nama }}
                      @else
                        {{ $k->nama_kelas ?? $k->nama }}
                      @endif
                      @if($k->tahun_ajaran)
                        ({{ $k->tahun_ajaran }})
                      @endif
                    </option>
                  @endforeach
                </select>
              </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="start_date_class" class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date_class" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="end_date_class" class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date_class" class="form-control">
              </div>
            </div>
          </div>

          <div class="mb-3">
                <label for="status_class">Filter Status</label>
                <select name="status" id="status_class" class="form-control">
                  <option value="">Semua Status</option>
                  <option value="hadir">Hadir</option>
                  <option value="izin">Izin</option>
                  <option value="sakit">Sakit</option>
                  <option value="alpha">Alpha</option>
                </select>
          </div>
        </div>
        <div class="card-footer bg-white border-0 p-4">
          <button type="submit" class="btn btn-primary btn-generate w-100">
            <i class="fas fa-chart-bar me-2"></i>
            Generate Laporan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Laporan Per Mahasiswa -->
  <div class="col-lg-6 mb-4">
    <div class="card report-card">
      <div class="card-header report-card-header success">
        <div class="d-flex align-items-center">
          <div class="report-icon me-3">
            <i class="fas fa-user-graduate text-white"></i>
          </div>
          <div class="text-white">
            <h5 class="mb-0 fw-bold">Laporan Per Mahasiswa</h5>
            <small class="opacity-75">Rekap absensi individual</small>
          </div>
        </div>
      </div>
      <form action="{{ route('reports.by-student') }}" method="GET">
        <div class="card-body p-4">
          <div class="mb-3">
                <label for="mahasiswa_id">Pilih Mahasiswa <span class="text-danger">*</span></label>
                <select name="mahasiswa_id" id="mahasiswa_id" class="form-control select2" required>
                  <option value="">-- Pilih Mahasiswa --</option>
                  @foreach(\App\Models\User::where('role', 'mahasiswa')->orderBy('name')->get() as $mhs)
                    <option value="{{ $mhs->id }}">
                      {{ $mhs->name }} ({{ $mhs->no_induk ?? 'No NIM' }})
                    </option>
                  @endforeach
                </select>
          </div>

          <div class="mb-3">
            <label for="mata_kuliah_id" class="form-label">Filter Mata Kuliah</label>
            <select name="mata_kuliah_id" id="mata_kuliah_id" class="form-control">
              <option value="">Semua Mata Kuliah</option>
              @foreach($mataKuliah as $mk)
                <option value="{{ $mk->id }}">{{ $mk->nama_mk }}</option>
              @endforeach
            </select>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="start_date_student" class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date_student" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="end_date_student" class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date_student" class="form-control">
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-white border-0 p-4">
          <button type="submit" class="btn btn-success btn-generate w-100">
            <i class="fas fa-chart-line me-2"></i>
            Generate Laporan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Info Card -->
<div class="row">
  <div class="col-12">
    <div class="card info-card">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-4">
          <div class="bg-white rounded-circle p-3 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-lightbulb fa-lg" style="color: #f59e0b;"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-bold">Fitur Laporan</h5>
            <small class="text-muted">Informasi lengkap tentang fitur laporan</small>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <h6 class="fw-bold mb-3" style="color: #667eea;">
              <i class="fas fa-users me-2"></i>
              Laporan Per Kelas
            </h6>
            <ul class="feature-list">
              <li>Rekap absensi seluruh mahasiswa dalam satu kelas</li>
              <li>Filter berdasarkan rentang tanggal</li>
              <li>Filter berdasarkan status kehadiran</li>
              <li>Export ke CSV untuk analisis lebih lanjut</li>
              <li>Persentase kehadiran per mahasiswa</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold mb-3" style="color: #10b981;">
              <i class="fas fa-user-graduate me-2"></i>
              Laporan Per Mahasiswa
            </h6>
            <ul class="feature-list">
              <li>Rekap absensi individual di semua kelas</li>
              <li>Filter berdasarkan mata kuliah tertentu</li>
              <li>Filter berdasarkan rentang tanggal</li>
              <li>Export ke CSV untuk dokumentasi</li>
              <li>Detail waktu absensi lengkap</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('AdminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
  $('.select2').select2({
    theme: 'bootstrap4',
    width: '100%'
  });
});
</script>
@endpush
