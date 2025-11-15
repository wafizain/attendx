@extends('layouts.master')

@section('title','Laporan & Rekap Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .report-card { transition: transform 0.2s; cursor: pointer; }
  .report-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="h4 mb-1">Laporan & Rekap Absensi</h1>
        <p class="text-muted mb-0">Pilih jenis laporan yang ingin Anda generate</p>
      </div>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Laporan</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <!-- Laporan Per Kelas -->
      <div class="col-md-6">
        <div class="card card-modern report-card">
          <div class="card-header bg-primary">
            <h3 class="card-title mb-0 text-white">
              <i class="fas fa-users mr-2"></i> Laporan Per Kelas
            </h3>
          </div>
          <form action="{{ route('reports.by-class') }}" method="GET">
            <div class="card-body">
              <p class="text-muted">Generate laporan absensi berdasarkan kelas dengan filter tanggal dan status kehadiran.</p>
              
              <div class="form-group">
                <label for="kelas_id">Pilih Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" id="kelas_id" class="form-control select2" required>
                  <option value="">-- Pilih Kelas --</option>
                  @foreach($kelas as $k)
                    <option value="{{ $k->id }}">
                      {{ $k->mataKuliah->nama_mk ?? 'Mata Kuliah Tidak Ada' }} - {{ $k->nama_kelas }} ({{ $k->tahun_ajaran }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="start_date_class">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date_class" class="form-control">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="end_date_class">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date_class" class="form-control">
                  </div>
                </div>
              </div>

              <div class="form-group">
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
            <div class="card-footer">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-chart-bar mr-1"></i> Generate Laporan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Laporan Per Mahasiswa -->
      <div class="col-md-6">
        <div class="card card-modern report-card">
          <div class="card-header bg-success">
            <h3 class="card-title mb-0 text-white">
              <i class="fas fa-user mr-2"></i> Laporan Per Mahasiswa
            </h3>
          </div>
          <form action="{{ route('reports.by-student') }}" method="GET">
            <div class="card-body">
              <p class="text-muted">Generate laporan absensi berdasarkan mahasiswa dengan filter mata kuliah dan tanggal.</p>
              
              <div class="form-group">
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

              <div class="form-group">
                <label for="mata_kuliah_id">Filter Mata Kuliah</label>
                <select name="mata_kuliah_id" id="mata_kuliah_id" class="form-control">
                  <option value="">Semua Mata Kuliah</option>
                  @foreach($mataKuliah as $mk)
                    <option value="{{ $mk->id }}">{{ $mk->nama_mk }}</option>
                  @endforeach
                </select>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="start_date_student">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date_student" class="form-control">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="end_date_student">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date_student" class="form-control">
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-success btn-block">
                <i class="fas fa-chart-line mr-1"></i> Generate Laporan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Info Card -->
    <div class="row">
      <div class="col-12">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">
              <i class="fas fa-info-circle mr-2"></i> Informasi
            </h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h5>Laporan Per Kelas</h5>
                <ul>
                  <li>Menampilkan rekap absensi seluruh mahasiswa dalam satu kelas</li>
                  <li>Filter berdasarkan rentang tanggal</li>
                  <li>Filter berdasarkan status kehadiran</li>
                  <li>Export ke CSV untuk analisis lebih lanjut</li>
                  <li>Menampilkan persentase kehadiran per mahasiswa</li>
                </ul>
              </div>
              <div class="col-md-6">
                <h5>Laporan Per Mahasiswa</h5>
                <ul>
                  <li>Menampilkan rekap absensi satu mahasiswa di semua kelas</li>
                  <li>Filter berdasarkan mata kuliah tertentu</li>
                  <li>Filter berdasarkan rentang tanggal</li>
                  <li>Export ke CSV untuk dokumentasi</li>
                  <li>Menampilkan detail waktu absensi</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
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
