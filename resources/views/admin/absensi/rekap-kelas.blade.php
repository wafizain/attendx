@extends('layouts.master')

@section('title', 'Rekap Absensi Per Kelas')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
<style>
  .page-header {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  
  .filter-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
  }
  
  .info-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  
  .stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }
  
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  
  .table-modern {
    border-collapse: separate;
    border-spacing: 0;
  }
  
  .table-modern thead th {
    background: #F9FAFB;
    color: #6B7280;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #E5E7EB;
    padding: 1rem;
  }
  
  .table-modern tbody tr {
    transition: all 0.2s;
  }
  
  .table-modern tbody tr:hover {
    background: #F9FAFB;
  }
  
  .table-modern tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #F3F4F6;
  }
  
  .progress-modern {
    height: 8px;
    border-radius: 4px;
    background: #E5E7EB;
  }
  
  .empty-state {
    text-align: center;
    padding: 4rem 1rem;
    color: #9ca3af;
  }
  
  .empty-state i {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.3;
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1 fw-bold">
        <i class="fas fa-users me-2 text-primary"></i>
        Rekap Absensi Per Kelas
      </h4>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">
        <i class="fas fa-info-circle me-1"></i>
        Rekap kehadiran mahasiswa berdasarkan kelas
      </p>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="card filter-card">
  <div class="card-body p-4">
    <form method="GET" action="{{ route('absensi.rekap-kelas') }}" class="row g-3 align-items-end">
      <div class="col-md-6">
        <label for="kelas_id" class="form-label fw-semibold">
          <i class="fas fa-chalkboard me-2 text-primary"></i>
          Pilih Kelas
        </label>
        <select name="kelas_id" id="kelas_id" class="form-control select2" style="border-radius: 8px;">
          <option value="">-- Pilih Kelas --</option>
          @foreach($kelasList as $kelas)
            <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
              {{ $kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }} - {{ $kelas->nama_kelas }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px; padding: 0.625rem 1rem;">
          <i class="fas fa-search me-2"></i>
          Tampilkan
        </button>
      </div>
    </form>
  </div>
</div>

@if($kelasId && count($data) > 0)
  <!-- Info Card -->
  <div class="card info-card">
    <div class="card-body p-4 text-white">
      <div class="row align-items-center">
        <div class="col-auto">
          <div class="bg-white bg-opacity-25 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-graduation-cap fa-2x"></i>
          </div>
        </div>
        <div class="col">
          <h5 class="mb-1 fw-bold">{{ $kelasList->find($kelasId)->mataKuliah->nama_mk ?? 'Kelas' }}</h5>
          <div class="d-flex flex-wrap gap-3 small opacity-75">
            <span><i class="fas fa-users me-1"></i>{{ count($data) }} Mahasiswa</span>
            <span><i class="fas fa-calendar-check me-1"></i>{{ $data[array_key_first($data)]['total_pertemuan'] ?? 0 }} Pertemuan</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    @php
      $totalHadir = collect($data)->sum('hadir');
      $totalIzin = collect($data)->sum('izin');
      $totalSakit = collect($data)->sum('sakit');
      $totalAlpha = collect($data)->sum('alpha');
      $avgPersentase = count($data) > 0 ? round(collect($data)->avg('persentase'), 1) : 0;
    @endphp
    
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <div class="card stat-card h-100">
        <div class="card-body text-center p-3">
          <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-check-circle fa-lg text-success"></i>
          </div>
          <h3 class="mb-1 fw-bold text-success">{{ $totalHadir }}</h3>
          <small class="text-muted fw-semibold">Total Hadir</small>
        </div>
      </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <div class="card stat-card h-100">
        <div class="card-body text-center p-3">
          <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-file-alt fa-lg text-warning"></i>
          </div>
          <h3 class="mb-1 fw-bold text-warning">{{ $totalIzin }}</h3>
          <small class="text-muted fw-semibold">Total Izin</small>
        </div>
      </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <div class="card stat-card h-100">
        <div class="card-body text-center p-3">
          <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-heartbeat fa-lg text-danger"></i>
          </div>
          <h3 class="mb-1 fw-bold text-danger">{{ $totalSakit }}</h3>
          <small class="text-muted fw-semibold">Total Sakit</small>
        </div>
      </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <div class="card stat-card h-100">
        <div class="card-body text-center p-3">
          <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-times-circle fa-lg text-secondary"></i>
          </div>
          <h3 class="mb-1 fw-bold text-secondary">{{ $totalAlpha }}</h3>
          <small class="text-muted fw-semibold">Total Alpha</small>
        </div>
      </div>
    </div>
    
    <div class="col-lg-4 col-md-8 mb-3">
      <div class="card stat-card h-100">
        <div class="card-body text-center p-3">
          <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-percentage fa-lg text-info"></i>
          </div>
          <h3 class="mb-1 fw-bold text-info">{{ $avgPersentase }}%</h3>
          <small class="text-muted fw-semibold">Rata-rata Kehadiran</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-header bg-white border-bottom py-3">
      <h5 class="mb-0 fw-bold">
        <i class="fas fa-list me-2 text-primary"></i>
        Daftar Mahasiswa
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-modern mb-0">
          <thead>
            <tr>
              <th width="50" class="text-center">#</th>
              <th>NIM</th>
              <th>Nama Mahasiswa</th>
              <th width="80" class="text-center">Hadir</th>
              <th width="80" class="text-center">Izin</th>
              <th width="80" class="text-center">Sakit</th>
              <th width="80" class="text-center">Alpha</th>
              <th width="150" class="text-center">Persentase</th>
              <th width="120" class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $index => $item)
            <tr>
              <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
              <td><span class="badge bg-light text-dark border">{{ $item['mahasiswa']->nim }}</span></td>
              <td><span class="fw-semibold">{{ $item['mahasiswa']->nama }}</span></td>
              <td class="text-center">
                <span class="badge bg-success fs-6 px-3 py-2">{{ $item['hadir'] }}</span>
              </td>
              <td class="text-center">
                <span class="badge bg-warning fs-6 px-3 py-2">{{ $item['izin'] }}</span>
              </td>
              <td class="text-center">
                <span class="badge bg-danger fs-6 px-3 py-2">{{ $item['sakit'] }}</span>
              </td>
              <td class="text-center">
                <span class="badge bg-secondary fs-6 px-3 py-2">{{ $item['alpha'] }}</span>
              </td>
              <td class="text-center">
                <div class="d-flex flex-column align-items-center">
                  <div class="progress progress-modern w-100 mb-1">
                    <div class="progress-bar bg-{{ $item['persentase'] >= 75 ? 'success' : ($item['persentase'] >= 50 ? 'warning' : 'danger') }}" 
                         role="progressbar" 
                         style="width: {{ $item['persentase'] }}%"
                         aria-valuenow="{{ $item['persentase'] }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                  </div>
                  <small class="fw-bold text-{{ $item['persentase'] >= 75 ? 'success' : ($item['persentase'] >= 50 ? 'warning' : 'danger') }}">
                    {{ $item['persentase'] }}%
                  </small>
                </div>
              </td>
              <td class="text-center">
                @if($item['persentase'] >= 75)
                <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                  <i class="fas fa-check-circle me-1"></i>Memenuhi
                </span>
                @else
                <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">
                  <i class="fas fa-exclamation-circle me-1"></i>Kurang
                </span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@elseif($kelasId)
  <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
      <div class="empty-state">
        <i class="fas fa-user-slash"></i>
        <h5 class="mb-2">Tidak Ada Data Mahasiswa</h5>
        <p class="text-muted mb-0">Kelas yang dipilih belum memiliki data mahasiswa atau belum ada pertemuan</p>
      </div>
    </div>
  </div>
@else
  <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-body">
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h5 class="mb-2">Pilih Kelas</h5>
        <p class="text-muted mb-0">Silakan pilih kelas untuk melihat rekap absensi mahasiswa</p>
      </div>
    </div>
  </div>
@endif

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
