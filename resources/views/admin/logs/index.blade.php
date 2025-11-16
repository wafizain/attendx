@extends('layouts.master')

@section('title','Log Aktivitas')

@push('styles')
  {{-- DataTables CSS --}}
  <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/daterangepicker/daterangepicker.css') }}">
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
    .badge-login { background: #10b981; color: white; }
    .badge-logout { background: #6b7280; color: white; }
    .badge-create { background: #3b82f6; color: white; }
    .badge-update { background: #f59e0b; color: white; }
    .badge-delete { background: #ef4444; color: white; }
    .badge-view { background: #8b5cf6; color: white; }
    .badge-default { background: #6b7280; color: white; }

    .text-truncate-log { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  </style>
@endpush

@section('content')
  <section class="content-header page-header-min">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0">Log Aktivitas</h1>
        <ol class="breadcrumb float-sm-right mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active">Log Aktivitas</li>
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
          <form method="GET" action="{{ route('logs.index') }}" id="filterForm">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="action" class="mb-1">Action</label>
                  <select name="action" id="action" class="form-control form-control-sm">
                    <option value="">Semua Action</option>
                    @foreach($actions as $action)
                      <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ ucfirst($action) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="module" class="mb-1">Module</label>
                  <select name="module" id="module" class="form-control form-control-sm">
                    <option value="">Semua Module</option>
                    @foreach($modules as $module)
                      <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                        {{ ucfirst($module) }}
                      </option>
                    @endforeach
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
                  <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Cari berdasarkan deskripsi, action, module, atau nama user..." value="{{ request('search') }}">
                </div>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-group mb-2">
                  <button type="submit" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-filter mr-1"></i> Filter
                  </button>
                  <a href="{{ route('logs.index') }}" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-redo mr-1"></i> Reset
                  </a>
                  <a href="{{ route('logs.export', request()->all()) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i> Export CSV
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
          <h3 class="card-title mb-0">Daftar Log Aktivitas ({{ $logs->total() }} records)</h3>
          <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#clearLogsModal">
            <i class="fas fa-trash mr-1"></i> Hapus Semua Log
          </button>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-modern table-hover mb-0 sticky-head">
              <thead>
                <tr>
                  <th style="width: 60px;">ID</th>
                  <th style="width: 140px;">Tanggal & Waktu</th>
                  <th style="width: 150px;">User</th>
                  <th style="width: 100px;">Action</th>
                  <th>Deskripsi</th>
                  <th style="width: 120px;">IP Address</th>
                  <th style="width: 100px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($logs as $log)
                  @php
                    $actionClass = 'badge-default';
                    switch(strtolower($log->action)) {
                      case 'login': $actionClass = 'badge-login'; break;
                      case 'logout': $actionClass = 'badge-logout'; break;
                      case 'create': $actionClass = 'badge-create'; break;
                      case 'update': $actionClass = 'badge-update'; break;
                      case 'delete': $actionClass = 'badge-delete'; break;
                      case 'view': $actionClass = 'badge-view'; break;
                    }
                  @endphp
                  <tr>
                    <td class="text-monospace">{{ $log->id }}</td>
                    <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                      @if($log->user)
                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $log->user->name }}">
                          {{ $log->user->name }}
                        </span>
                      @else
                        <span class="text-muted">System</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge badge-action {{ $actionClass }}">{{ strtoupper($log->action) }}</span>
                    </td>
                    <td>
                      <div class="text-truncate-log" title="{{ $log->description }}">
                        {{ $log->description ?? '-' }}
                      </div>
                    </td>
                    <td class="text-monospace text-muted">{{ $log->ip_address ?? '-' }}</td>
                    <td class="text-nowrap">
                      <a href="{{ route('logs.show', $log->id) }}" class="btn btn-info btn-xs" title="Detail">
                        <i class="fas fa-eye"></i>
                      </a>
                      <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#deleteModal{{ $log->id }}" title="Hapus">
                        <i class="fas fa-trash"></i>
                      </button>

                      <!-- Delete Modal -->
                      <div class="modal fade" id="deleteModal{{ $log->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Konfirmasi Hapus</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              Yakin ingin menghapus log ini?
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                              <form action="{{ route('logs.destroy', $log->id) }}" method="POST" style="display:inline-block;">
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
                    <td colspan="7" class="text-center text-muted py-4">Belum ada data log.</td>
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

  <!-- Clear All Logs Modal -->
  <div class="modal fade" id="clearLogsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-danger">
          <h5 class="modal-title text-white">Konfirmasi Hapus Semua Log</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p><strong>PERINGATAN!</strong></p>
          <p>Anda akan menghapus <strong>SEMUA</strong> log aktivitas. Tindakan ini tidak dapat dibatalkan!</p>
          <p>Apakah Anda yakin ingin melanjutkan?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <form action="{{ route('logs.clear') }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn btn-danger">Ya, Hapus Semua</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function () {
      // Auto submit on filter change (optional)
      $('#action, #module').on('change', function() {
        // Uncomment to auto-submit
        // $('#filterForm').submit();
      });
    });
  </script>
@endpush
