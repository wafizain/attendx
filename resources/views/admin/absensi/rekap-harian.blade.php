@extends('layouts.master')

@section('title', 'Rekap Absensi Harian')

@push('styles')
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
  
  .sesi-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
    overflow: hidden;
  }
  
  .sesi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  
  .sesi-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
  }
  
  .stat-badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .stat-badge.hadir {
    background: #ECFDF5;
    color: #059669;
    border: 1px solid #10b981;
  }
  
  .stat-badge.izin {
    background: #FFFBEB;
    color: #d97706;
    border: 1px solid #f59e0b;
  }
  
  .stat-badge.sakit {
    background: #FEF2F2;
    color: #dc2626;
    border: 1px solid #ef4444;
  }
  
  .stat-badge.alpha {
    background: #F3F4F6;
    color: #6b7280;
    border: 1px solid #9ca3af;
  }
  
  .empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
  }
  
  .empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1 fw-bold">
        <i class="fas fa-calendar-day me-2 text-primary"></i>
        Rekap Absensi Harian
      </h4>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">
        <i class="fas fa-info-circle me-1"></i>
        Rekap kehadiran mahasiswa per tanggal
      </p>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="card filter-card">
  <div class="card-body p-4">
    <form method="GET" action="{{ route('absensi.rekap-harian') }}" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label for="tanggal" class="form-label fw-semibold">
          <i class="fas fa-calendar me-2 text-primary"></i>
          Pilih Tanggal
        </label>
        <input type="date" 
               name="tanggal" 
               id="tanggal" 
               class="form-control" 
               value="{{ $tanggal }}"
               style="border-radius: 8px; border: 1px solid #E5E7EB;">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px; padding: 0.625rem 1rem;">
          <i class="fas fa-search me-2"></i>
          Tampilkan
        </button>
      </div>
      <div class="col-md-5 text-end">
        <div class="text-muted small">
          <i class="fas fa-info-circle me-1"></i>
          Total Sesi: <strong>{{ $sesiList->count() }}</strong>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Sesi List -->
@forelse($sesiList as $sesi)
<div class="card sesi-card">
  <div class="card-header sesi-header">
    <div class="d-flex justify-content-between align-items-center">
      <div class="text-white">
        <h5 class="mb-1 fw-bold">
          <i class="fas fa-book me-2"></i>
          {{ $sesi->kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}
        </h5>
        <div class="d-flex flex-wrap gap-2 small opacity-75">
          <span><i class="fas fa-users me-1"></i>{{ $sesi->kelas->nama_kelas ?? '-' }}</span>
          <span><i class="fas fa-user-tie me-1"></i>{{ $sesi->kelas->dosen->name ?? '-' }}</span>
          <span><i class="fas fa-clock me-1"></i>{{ substr($sesi->waktu_mulai, 0, 5) }} - {{ substr($sesi->waktu_selesai, 0, 5) }}</span>
        </div>
      </div>
      <div class="text-white text-end">
        <div class="badge bg-white bg-opacity-25 px-3 py-2">
          <i class="fas fa-calendar-check me-1"></i>
          {{ \Carbon\Carbon::parse($sesi->tanggal)->format('d M Y') }}
        </div>
      </div>
    </div>
  </div>
  
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-md-2 col-6">
        <div class="stat-badge hadir">
          <i class="fas fa-check-circle"></i>
          <div>
            <div style="font-size: 1.25rem;">{{ $statistik[$sesi->id]['hadir'] ?? 0 }}</div>
            <small style="opacity: 0.75;">Hadir</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-badge izin">
          <i class="fas fa-file-alt"></i>
          <div>
            <div style="font-size: 1.25rem;">{{ $statistik[$sesi->id]['izin'] ?? 0 }}</div>
            <small style="opacity: 0.75;">Izin</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-badge sakit">
          <i class="fas fa-heartbeat"></i>
          <div>
            <div style="font-size: 1.25rem;">{{ $statistik[$sesi->id]['sakit'] ?? 0 }}</div>
            <small style="opacity: 0.75;">Sakit</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-badge alpha">
          <i class="fas fa-times-circle"></i>
          <div>
            <div style="font-size: 1.25rem;">{{ $statistik[$sesi->id]['alpha'] ?? 0 }}</div>
            <small style="opacity: 0.75;">Alpha</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-flex align-items-center h-100">
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="text-muted fw-semibold">Total Kehadiran</small>
              <small class="fw-bold">{{ $statistik[$sesi->id]['total'] ?? 0 }} Mahasiswa</small>
            </div>
            @php
              $total = $statistik[$sesi->id]['total'] ?? 1;
              $hadir = $statistik[$sesi->id]['hadir'] ?? 0;
              $persentase = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
            @endphp
            <div class="progress" style="height: 8px; border-radius: 4px;">
              <div class="progress-bar bg-success" 
                   role="progressbar" 
                   style="width: {{ $persentase }}%"
                   aria-valuenow="{{ $persentase }}" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
              </div>
            </div>
            <small class="text-muted">{{ $persentase }}% Hadir</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@empty
<div class="card sesi-card">
  <div class="card-body">
    <div class="empty-state">
      <i class="fas fa-calendar-times"></i>
      <h5 class="mb-2">Tidak Ada Sesi Absensi</h5>
      <p class="text-muted mb-0">Tidak ada sesi absensi pada tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
    </div>
  </div>
</div>
@endforelse

@endsection
