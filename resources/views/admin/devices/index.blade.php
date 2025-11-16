@extends('layouts.master')

@section('title','Manajemen Perangkat')

@push('styles')
<style>
  .page-header {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  
  .stat-card {
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
    color: white;
  }
  
  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  
  .stat-card.primary {
    --gradient-start: #667eea;
    --gradient-end: #764ba2;
  }
  
  .stat-card.success {
    --gradient-start: #10b981;
    --gradient-end: #059669;
  }
  
  .stat-card.info {
    --gradient-start: #3b82f6;
    --gradient-end: #2563eb;
  }
  
  .stat-card.danger {
    --gradient-start: #ef4444;
    --gradient-end: #dc2626;
  }
  
  .stat-icon {
    font-size: 2.5rem;
    opacity: 0.9;
  }
  
  .stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0.5rem 0;
  }
  
  .stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .device-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
  }
  
  .device-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  
  .filter-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    position: relative;
  }
  
  .status-online {
    background-color: #10b981;
    animation: pulse 2s infinite;
  }
  
  .status-online::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: #10b981;
    animation: ripple 2s infinite;
  }
  
  .status-offline {
    background-color: #ef4444;
  }
  
  .status-inactive {
    background-color: #6b7280;
  }
  
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
  }
  
  @keyframes ripple {
    0% {
      transform: scale(1);
      opacity: 1;
    }
    100% {
      transform: scale(2);
      opacity: 0;
    }
  }
  
  .device-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
  }
  
  .device-table thead {
    background: #f8f9fa;
  }
  
  .device-table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 1rem;
    border: none;
  }
  
  .device-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
  }
  
  .device-table tbody tr:hover {
    background-color: #f9fafb;
  }
  
  .btn-action {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin: 0 2px;
    transition: all 0.2s ease;
  }
  
  .btn-action:hover {
    transform: translateY(-2px);
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1 fw-bold">
        <i class="fas fa-microchip me-2 text-primary"></i>
        Manajemen Perangkat Absensi
      </h4>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">
        <i class="fas fa-info-circle me-1"></i>
        Kelola dan monitor perangkat absensi fingerprint & kamera
      </p>
    </div>
    <a href="{{ route('devices.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-2"></i>
      Tambah Perangkat
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<!-- Statistics -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card info">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-label">Total Perangkat</div>
          <div class="stat-value">{{ $stats['total'] }}</div>
          <small>Semua perangkat</small>
        </div>
        <div class="stat-icon">
          <i class="fas fa-microchip"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card success">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-label">Active</div>
          <div class="stat-value">{{ $stats['active'] }}</div>
          <small>Perangkat aktif</small>
        </div>
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card primary">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-label">Online</div>
          <div class="stat-value">{{ $stats['online'] }}</div>
          <small>Terhubung sekarang</small>
        </div>
        <div class="stat-icon">
          <i class="fas fa-wifi"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card danger">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-label">Error</div>
          <div class="stat-value">{{ $stats['error'] }}</div>
          <small>Perlu perhatian</small>
        </div>
        <div class="stat-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="filter-card">
  <form method="GET">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-semibold small text-muted">CARI PERANGKAT</label>
        <div class="input-group">
          <span class="input-group-text bg-white">
            <i class="fas fa-search text-muted"></i>
          </span>
          <input type="text" name="search" class="form-control" placeholder="Nama atau Device ID..." value="{{ request('search') }}">
        </div>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold small text-muted">TIPE</label>
        <select name="type" class="form-select">
          <option value="">Semua Tipe</option>
          <option value="fingerprint" {{ request('type') == 'fingerprint' ? 'selected' : '' }}>Fingerprint</option>
          <option value="camera" {{ request('type') == 'camera' ? 'selected' : '' }}>Camera</option>
          <option value="hybrid" {{ request('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold small text-muted">STATUS</label>
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
          <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
          <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
        </select>
      </div>
      <div class="col-md-4">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-filter me-2"></i>
            Filter
          </button>
          <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-redo"></i>
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- Devices Table -->
<div class="device-card">
  <div class="card-header bg-white border-bottom">
    <h5 class="mb-0 fw-semibold">
      <i class="fas fa-list me-2 text-primary"></i>
      Daftar Perangkat
    </h5>
  </div>
  <div class="table-responsive">
    <table class="table device-table mb-0">
      <thead>
        <tr>
          <th>Status</th>
          <th>Device ID</th>
          <th>Nama Perangkat</th>
          <th>Tipe</th>
          <th>Lokasi</th>
          <th>IP Address</th>
          <th>Firmware</th>
          <th>Last Seen</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
              @forelse($devices as $device)
                @php
                  $connection = $device->getConnectionStatus();
                  $typeBadge = $device->getTypeBadge();
                  $statusBadge = $device->getStatusBadge();
                @endphp
                <tr>
                  <td>
                    <span class="status-indicator status-{{ $device->isOnline() ? 'online' : 'offline' }}" 
                          title="{{ $connection['status'] }}"></span>
                    <span class="badge badge-{{ $statusBadge['color'] }}">{{ $statusBadge['label'] }}</span>
                  </td>
                  <td><code>{{ $device->device_id }}</code></td>
                  <td><strong>{{ $device->device_name }}</strong></td>
                  <td><span class="badge badge-{{ $typeBadge['color'] }}">{{ $typeBadge['label'] }}</span></td>
                  <td>{{ $device->location ?? '-' }}</td>
                  <td><small class="text-muted">{{ $device->ip_address ?? '-' }}</small></td>
                  <td><small>{{ $device->firmware_version ?? '-' }}</small></td>
                  <td>
                    <small class="text-{{ $connection['color'] }}">
                      {{ $device->getLastSeenHuman() }}
                    </small>
                  </td>
                  <td class="text-center">
                    <a href="{{ route('devices.show', $device->id) }}" class="btn btn-info btn-action" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-warning btn-action" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-action" data-bs-toggle="modal" 
                            data-bs-target="#deleteModal{{ $device->id }}" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $device->id }}" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                          </div>
                          <div class="modal-body">
                            Yakin ingin menghapus perangkat <strong>{{ $device->device_name }}</strong>?
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <form action="{{ route('devices.destroy', $device->id) }}" method="POST" style="display:inline;">
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
                  <td colspan="9" class="text-center py-5">
                    <i class="fas fa-microchip fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">Belum ada perangkat terdaftar</p>
                    <small class="text-muted">Klik tombol "Tambah Perangkat" untuk menambahkan perangkat baru</small>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
// Auto refresh every 30 seconds
setInterval(function() {
  location.reload();
}, 30000);
</script>
@endpush
