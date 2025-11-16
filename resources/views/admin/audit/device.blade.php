@extends('layouts.master')

@section('title','Log Perangkat')

@push('styles')
  {{-- DataTables CSS --}}
  <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <style>
    /* Nuansa modern & clean */
    .page-header-min { padding: .75rem 0; }
    .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
    .card-modern .card-header { background:#fff; border-bottom: 1px solid #eef1f5; padding: .75rem 1rem; }
    .card-modern .card-title { font-weight: 600; color: #111827; }

    .table-modern thead th { background:#f9fafb; color:#111827; font-weight:600; border-bottom:1px solid #eef1f5; font-size: 0.875rem; }
    .table-modern td, .table-modern th { vertical-align: middle; padding: 0.5rem; font-size: 0.875rem; }
    .table-modern tbody tr:hover { background: #fafafa; }
    .sticky-head thead th { position: sticky; top: 0; z-index: 2; }

    .btn-xs { padding: .25rem .5rem; font-size: .75rem; border-radius: .5rem; }
    .badge-action { letter-spacing: .2px; font-weight: 600; font-size: 0.75rem; }

    /* Filter section */
    .filter-section { background: #f9fafb; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; }
    .filter-section .form-control, .filter-section .form-select { border-radius: 8px; font-size: 0.875rem; }

    /* Log action colors */
    .badge-heartbeat { background: #10b981; color: white; }
    .badge-sync { background: #3b82f6; color: white; }
    .badge-pairing { background: #8b5cf6; color: white; }
    .badge-attendance { background: #f59e0b; color: white; }
    .badge-error { background: #ef4444; color: white; }
    .badge-default { background: #6b7280; color: white; }

    /* Response code colors */
    .badge-success-code { background: #10b981; color: white; }
    .badge-error-code { background: #ef4444; color: white; }
    .badge-warning-code { background: #f59e0b; color: white; }

    .text-truncate-log { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    
    /* Stats cards */
    .stats-card {
      border-radius: 12px;
      border: 1px solid #eef1f5;
      padding: 1rem;
      background: white;
      transition: all 0.3s ease;
    }
    .stats-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transform: translateY(-2px);
    }
    .stats-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
    .stats-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: #111827;
      margin: 0;
    }
    .stats-label {
      font-size: 0.875rem;
      color: #6b7280;
      margin: 0;
    }
  </style>
@endpush

@section('content')
  <section class="content-header page-header-min">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0">Log Perangkat</h1>
        <ol class="breadcrumb float-sm-right mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
          <li class="breadcrumb-item">Perangkat & Integrasi</li>
          <li class="breadcrumb-item active">Log Perangkat</li>
        </ol>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
          {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
          {{ session('error') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      @endif

      {{-- Statistics Cards --}}
      <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stats-card">
            <div class="d-flex align-items-center">
              <div class="stats-icon" style="background: #EEF2FF; color: #3b82f6;">
                <i class="fas fa-server"></i>
              </div>
              <div class="ml-3">
                <p class="stats-value">{{ $logs->total() }}</p>
                <p class="stats-label">Total Log</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stats-card">
            <div class="d-flex align-items-center">
              <div class="stats-icon" style="background: #ECFDF5; color: #10b981;">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="ml-3">
                <p class="stats-value">{{ $logs->where('response_code', '>=', 200)->where('response_code', '<', 300)->count() }}</p>
                <p class="stats-label">Sukses</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stats-card">
            <div class="d-flex align-items-center">
              <div class="stats-icon" style="background: #FEF3C7; color: #f59e0b;">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <div class="ml-3">
                <p class="stats-value">{{ $logs->where('response_code', '>=', 400)->where('response_code', '<', 500)->count() }}</p>
                <p class="stats-label">Error Client</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stats-card">
            <div class="d-flex align-items-center">
              <div class="stats-icon" style="background: #FEE2E2; color: #ef4444;">
                <i class="fas fa-times-circle"></i>
              </div>
              <div class="ml-3">
                <p class="stats-value">{{ $logs->where('response_code', '>=', 500)->count() }}</p>
                <p class="stats-label">Error Server</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Filter Section --}}
      <div class="card card-modern">
        <div class="card-header">
          <h3 class="card-title mb-0">Filter Log</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body filter-section">
          <form method="GET" action="{{ route('logs.device') }}" id="filterForm">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="device_id" class="mb-1">Perangkat</label>
                  <select name="device_id" id="device_id" class="form-control form-control-sm">
                    <option value="">Semua Perangkat</option>
                    @foreach(\App\Models\Device::orderBy('device_name')->get() as $device)
                      <option value="{{ $device->id }}" {{ request('device_id') == $device->id ? 'selected' : '' }}>
                        {{ $device->device_name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="action" class="mb-1">Action</label>
                  <select name="action" id="action" class="form-control form-control-sm">
                    <option value="">Semua Action</option>
                    <option value="heartbeat" {{ request('action') == 'heartbeat' ? 'selected' : '' }}>Heartbeat</option>
                    <option value="sync" {{ request('action') == 'sync' ? 'selected' : '' }}>Sync</option>
                    <option value="pairing" {{ request('action') == 'pairing' ? 'selected' : '' }}>Pairing</option>
                    <option value="attendance" {{ request('action') == 'attendance' ? 'selected' : '' }}>Attendance</option>
                    <option value="error" {{ request('action') == 'error' ? 'selected' : '' }}>Error</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="date_from" class="mb-1">Dari Tanggal</label>
                  <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="date_to" class="mb-1">Sampai Tanggal</label>
                  <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="search" class="mb-1">Pencarian</label>
                  <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Cari berdasarkan endpoint, IP address, atau response message..." value="{{ request('search') }}">
                </div>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-group mb-2">
                  <button type="submit" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-filter mr-1"></i> Filter
                  </button>
                  <a href="{{ route('logs.device') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-redo mr-1"></i> Reset
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      {{-- Logs Table --}}
      <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title mb-0">Daftar Log Perangkat ({{ $logs->total() }} records)</h3>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-modern table-hover mb-0 sticky-head">
              <thead>
                <tr>
                  <th style="width: 60px;">ID</th>
                  <th style="width: 140px;">Tanggal & Waktu</th>
                  <th style="width: 180px;">Perangkat</th>
                  <th style="width: 100px;">Action</th>
                  <th style="width: 200px;">Endpoint</th>
                  <th style="width: 120px;">IP Address</th>
                  <th style="width: 80px;">Response</th>
                  <th>Response Message</th>
                  <th style="width: 100px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($logs as $log)
                  @php
                    $actionClass = 'badge-default';
                    switch(strtolower($log->action)) {
                      case 'heartbeat': $actionClass = 'badge-heartbeat'; break;
                      case 'sync': $actionClass = 'badge-sync'; break;
                      case 'pairing': $actionClass = 'badge-pairing'; break;
                      case 'attendance': $actionClass = 'badge-attendance'; break;
                      case 'error': $actionClass = 'badge-error'; break;
                    }

                    $responseClass = 'badge-default';
                    if ($log->response_code >= 200 && $log->response_code < 300) {
                      $responseClass = 'badge-success-code';
                    } elseif ($log->response_code >= 400 && $log->response_code < 500) {
                      $responseClass = 'badge-warning-code';
                    } elseif ($log->response_code >= 500) {
                      $responseClass = 'badge-error-code';
                    }
                  @endphp
                  <tr>
                    <td class="text-monospace">{{ $log->id }}</td>
                    <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                      @if($log->device)
                        <div class="d-flex align-items-center">
                          <i class="fas fa-microchip mr-2 text-primary"></i>
                          <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $log->device->device_name }}">
                            {{ $log->device->device_name }}
                          </span>
                        </div>
                      @else
                        <span class="text-muted">Unknown Device</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge badge-action {{ $actionClass }}">{{ strtoupper($log->action) }}</span>
                    </td>
                    <td class="text-monospace" style="font-size: 0.8rem;">
                      <div class="text-truncate" style="max-width: 200px;" title="{{ $log->endpoint }}">
                        {{ $log->endpoint ?? '-' }}
                      </div>
                    </td>
                    <td class="text-monospace text-muted">{{ $log->ip_address ?? '-' }}</td>
                    <td>
                      <span class="badge {{ $responseClass }}">{{ $log->response_code ?? '-' }}</span>
                    </td>
                    <td>
                      <div class="text-truncate-log" title="{{ $log->response_message }}">
                        {{ $log->response_message ?? '-' }}
                      </div>
                    </td>
                    <td class="text-nowrap">
                      <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#detailModal{{ $log->id }}" title="Detail">
                        <i class="fas fa-eye"></i>
                      </button>

                      <!-- Detail Modal -->
                      <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                          <div class="modal-content">
                            <div class="modal-header bg-info">
                              <h5 class="modal-title text-white">Detail Log Perangkat #{{ $log->id }}</h5>
                              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-md-6">
                                  <table class="table table-sm table-borderless">
                                    <tr>
                                      <th style="width: 150px;">Tanggal & Waktu:</th>
                                      <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                      <th>Perangkat:</th>
                                      <td>{{ $log->device ? $log->device->device_name : 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                      <th>Action:</th>
                                      <td><span class="badge {{ $actionClass }}">{{ strtoupper($log->action) }}</span></td>
                                    </tr>
                                    <tr>
                                      <th>Endpoint:</th>
                                      <td class="text-monospace" style="font-size: 0.85rem;">{{ $log->endpoint ?? '-' }}</td>
                                    </tr>
                                  </table>
                                </div>
                                <div class="col-md-6">
                                  <table class="table table-sm table-borderless">
                                    <tr>
                                      <th style="width: 150px;">IP Address:</th>
                                      <td class="text-monospace">{{ $log->ip_address ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                      <th>User Agent:</th>
                                      <td style="font-size: 0.85rem;">{{ $log->user_agent ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                      <th>Response Code:</th>
                                      <td><span class="badge {{ $responseClass }}">{{ $log->response_code ?? '-' }}</span></td>
                                    </tr>
                                    <tr>
                                      <th>Response Message:</th>
                                      <td>{{ $log->response_message ?? '-' }}</td>
                                    </tr>
                                  </table>
                                </div>
                              </div>
                              
                              @if($log->request_data)
                                <hr>
                                <h6 class="font-weight-bold">Request Data:</h6>
                                <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto; font-size: 0.85rem;">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                              @endif
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">Belum ada data log perangkat.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        @if($logs->hasPages())
          <div class="card-footer">
            {{ $logs->appends(request()->query())->links() }}
          </div>
        @endif
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    $(function () {
      // Auto refresh every 30 seconds (optional)
      // setInterval(function() {
      //   location.reload();
      // }, 30000);
    });
  </script>
@endpush
