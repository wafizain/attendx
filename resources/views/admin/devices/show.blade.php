@extends('layouts.master')

@section('title','Detail Perangkat')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .info-box { border-radius: 10px; }
  .status-indicator { 
    width: 15px; 
    height: 15px; 
    border-radius: 50%; 
    display: inline-block; 
    margin-right: 8px;
  }
  .status-online { background-color: #28a745; animation: pulse 2s infinite; }
  .status-offline { background-color: #dc3545; }
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
      <h1 class="h4 mb-0">Detail Perangkat</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Perangkat</a></li>
        <li class="breadcrumb-item active">Detail</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    @php
      $connection = $device->getConnectionStatus();
      $typeBadge = $device->getTypeBadge();
      $statusBadge = $device->getStatusBadge();
    @endphp

    <!-- Status Info Boxes -->
    <div class="row">
      <div class="col-md-3">
        <div class="info-box bg-{{ $connection['color'] }}">
          <span class="info-box-icon"><i class="fas fa-wifi"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Connection</span>
            <span class="info-box-number">{{ $connection['status'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-{{ $statusBadge['color'] }}">
          <span class="info-box-icon"><i class="fas fa-power-off"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Status</span>
            <span class="info-box-number">{{ $statusBadge['label'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-{{ $typeBadge['color'] }}">
          <span class="info-box-icon"><i class="fas fa-microchip"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Type</span>
            <span class="info-box-number">{{ $typeBadge['label'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-clock"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Last Seen</span>
            <span class="info-box-number" style="font-size: 14px;">{{ $device->getLastSeenHuman() }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Device Information -->
      <div class="col-md-6">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">
              <span class="status-indicator status-{{ $device->isOnline() ? 'online' : 'offline' }}"></span>
              Informasi Perangkat
            </h3>
            <div class="card-tools">
              <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-tool">
                <i class="fas fa-edit"></i>
              </a>
            </div>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <th width="40%">Device ID</th>
                <td><code>{{ $device->device_id }}</code></td>
              </tr>
              <tr>
                <th>Nama Perangkat</th>
                <td><strong>{{ $device->device_name }}</strong></td>
              </tr>
              <tr>
                <th>Tipe</th>
                <td><span class="badge badge-{{ $typeBadge['color'] }}">{{ $typeBadge['label'] }}</span></td>
              </tr>
              <tr>
                <th>Model</th>
                <td>{{ $device->model ?? '-' }}</td>
              </tr>
              <tr>
                <th>Lokasi</th>
                <td>{{ $device->location ?? '-' }}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td><span class="badge badge-{{ $statusBadge['color'] }}">{{ $statusBadge['label'] }}</span></td>
              </tr>
              <tr>
                <th>Terdaftar</th>
                <td><small>{{ $device->created_at->format('d/m/Y H:i') }}</small></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Network Information -->
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Informasi Jaringan</h3>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <th width="40%">IP Address</th>
                <td><code>{{ $device->ip_address ?? '-' }}</code></td>
              </tr>
              <tr>
                <th>MAC Address</th>
                <td><code>{{ $device->mac_address ?? '-' }}</code></td>
              </tr>
              <tr>
                <th>Firmware Version</th>
                <td>{{ $device->firmware_version ?? '-' }}</td>
              </tr>
              <tr>
                <th>Last Seen</th>
                <td>
                  <span class="text-{{ $connection['color'] }}">
                    {{ $device->last_seen ? $device->last_seen->format('d/m/Y H:i:s') : 'Never' }}
                  </span>
                  <br><small class="text-muted">{{ $device->getLastSeenHuman() }}</small>
                </td>
              </tr>
              <tr>
                <th>Last Sync</th>
                <td>{{ $device->last_sync ? $device->last_sync->format('d/m/Y H:i:s') : 'Never' }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Actions & Configuration -->
      <div class="col-md-6">
        <!-- Quick Actions -->
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Quick Actions</h3>
          </div>
          <div class="card-body">
            <button type="button" class="btn btn-primary btn-block mb-2" id="testConnection">
              <i class="fas fa-plug"></i> Test Connection
            </button>
            <button type="button" class="btn btn-info btn-block mb-2" data-toggle="modal" data-target="#configModal">
              <i class="fas fa-cog"></i> Update Configuration
            </button>
            <button type="button" class="btn btn-warning btn-block mb-2">
              <i class="fas fa-sync"></i> Sync Data
            </button>
            <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-secondary btn-block mb-2">
              <i class="fas fa-edit"></i> Edit Device
            </a>
            <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteModal">
              <i class="fas fa-trash"></i> Delete Device
            </button>
          </div>
        </div>

        <!-- Configuration -->
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Konfigurasi</h3>
          </div>
          <div class="card-body">
            @if($device->config)
              <pre class="bg-light p-3 rounded"><code>{{ json_encode($device->config, JSON_PRETTY_PRINT) }}</code></pre>
            @else
              <p class="text-muted text-center py-3">Belum ada konfigurasi</p>
            @endif
          </div>
        </div>

        <!-- Notes -->
        @if($device->notes)
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Catatan</h3>
          </div>
          <div class="card-body">
            <p class="mb-0">{{ $device->notes }}</p>
          </div>
        </div>
        @endif
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <a href="{{ route('devices.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Config Modal -->
<div class="modal fade" id="configModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Configuration</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form action="{{ route('devices.sync-config', $device->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Configuration (JSON)</label>
            <textarea name="config" class="form-control" rows="10" placeholder='{"key": "value"}'>{{ $device->config ? json_encode($device->config, JSON_PRETTY_PRINT) : '' }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Sync Configuration</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
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
@endsection

@push('scripts')
<script>
document.getElementById('testConnection').addEventListener('click', function() {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
  
  fetch("{{ route('devices.test-connection', $device->id) }}")
    .then(res => res.json())
    .then(data => {
      if (data.online) {
        alert('✅ Device is ONLINE!\n\nLast seen: ' + data.last_seen);
      } else {
        alert('❌ Device is OFFLINE\n\nLast seen: ' + data.last_seen);
      }
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
    })
    .catch(err => {
      alert('Error testing connection');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
    });
});
</script>
@endpush
