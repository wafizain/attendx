@extends('layouts.master')

@section('title','Manajemen Perangkat')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .info-box { border-radius: 10px; }
  .status-indicator { 
    width: 10px; 
    height: 10px; 
    border-radius: 50%; 
    display: inline-block; 
    margin-right: 5px;
  }
  .status-online { background-color: #28a745; animation: pulse 2s infinite; }
  .status-offline { background-color: #dc3545; }
  .status-inactive { background-color: #6c757d; }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Manajemen Perangkat</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Perangkat</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    <!-- Statistics -->
    <div class="row">
      <div class="col-md-3">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-microchip"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Perangkat</span>
            <span class="info-box-number">{{ $stats['total'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Active</span>
            <span class="info-box-number">{{ $stats['active'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-primary">
          <span class="info-box-icon"><i class="fas fa-wifi"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Online</span>
            <span class="info-box-number">{{ $stats['online'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-danger">
          <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Error</span>
            <span class="info-box-number">{{ $stats['error'] }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card card-modern">
      <div class="card-header">
        <h3 class="card-title mb-0">Daftar Perangkat</h3>
        <div class="card-tools">
          <a href="{{ route('devices.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Perangkat
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" class="mb-3">
          <div class="row">
            <div class="col-md-3">
              <input type="text" name="search" class="form-control" placeholder="Cari perangkat..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
              <select name="type" class="form-control">
                <option value="">Semua Tipe</option>
                <option value="fingerprint" {{ request('type') == 'fingerprint' ? 'selected' : '' }}>Fingerprint</option>
                <option value="camera" {{ request('type') == 'camera' ? 'selected' : '' }}>Camera</option>
                <option value="hybrid" {{ request('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
              </select>
            </div>
            <div class="col-md-2">
              <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
              </button>
              <a href="{{ route('devices.index') }}" class="btn btn-secondary">Reset</a>
            </div>
          </div>
        </form>

        <!-- Devices Table -->
        <div class="table-responsive">
          <table class="table table-hover">
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
                <th>Aksi</th>
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
                  <td>
                    <a href="{{ route('devices.show', $device->id) }}" class="btn btn-info btn-xs" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-warning btn-xs" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" 
                            data-target="#deleteModal{{ $device->id }}" title="Hapus">
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
                  <td colspan="9" class="text-center text-muted py-4">
                    Belum ada perangkat terdaftar
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
// Auto refresh every 30 seconds
setInterval(function() {
  location.reload();
}, 30000);
</script>
@endpush
