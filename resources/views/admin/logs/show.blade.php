@extends('layouts.master')

@section('title','Detail Log')

@push('styles')
  <style>
    .page-header-min { padding: .75rem 0; }
    .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
    .card-modern .card-header { background:#fff; border-bottom: 1px solid #eef1f5; padding: .75rem 1rem; }
    .card-modern .card-title { font-weight: 600; color: #111827; }

    .detail-row { padding: 0.75rem 0; border-bottom: 1px solid #eef1f5; }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { font-weight: 600; color: #374151; margin-bottom: 0.25rem; }
    .detail-value { color: #6b7280; }
    
    .badge-action { letter-spacing: .2px; font-weight: 600; font-size: 0.875rem; padding: 0.5rem 0.75rem; }
    
    /* Log action colors */
    .badge-login { background: #10b981; color: white; }
    .badge-logout { background: #6b7280; color: white; }
    .badge-create { background: #3b82f6; color: white; }
    .badge-update { background: #f59e0b; color: white; }
    .badge-delete { background: #ef4444; color: white; }
    .badge-view { background: #8b5cf6; color: white; }
    .badge-default { background: #6b7280; color: white; }

    .json-viewer { background: #f9fafb; padding: 1rem; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 0.875rem; overflow-x: auto; }
  </style>
@endpush

@section('content')
  <section class="content-header page-header-min">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0">Detail Log Aktivitas</h1>
        <ol class="breadcrumb float-sm-right mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('logs.index') }}">Log Aktivitas</a></li>
          <li class="breadcrumb-item active">Detail</li>
        </ol>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-8">
          <div class="card card-modern">
            <div class="card-header">
              <h3 class="card-title mb-0">Informasi Log</h3>
            </div>
            <div class="card-body">
              <div class="detail-row">
                <div class="detail-label">Log ID</div>
                <div class="detail-value text-monospace">#{{ $log->id }}</div>
              </div>

              <div class="detail-row">
                <div class="detail-label">Tanggal & Waktu</div>
                <div class="detail-value">
                  {{ $log->created_at->format('d F Y, H:i:s') }}
                  <span class="text-muted">({{ $log->created_at->diffForHumans() }})</span>
                </div>
              </div>

              <div class="detail-row">
                <div class="detail-label">User</div>
                <div class="detail-value">
                  @if($log->user)
                    <strong>{{ $log->user->name }}</strong>
                    <br>
                    <small class="text-muted">{{ $log->user->email }} ({{ ucfirst($log->user->role) }})</small>
                  @else
                    <span class="text-muted">System / Unknown User</span>
                  @endif
                </div>
              </div>

              <div class="detail-row">
                <div class="detail-label">Action</div>
                <div class="detail-value">
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
                  <span class="badge badge-action {{ $actionClass }}">{{ strtoupper($log->action) }}</span>
                </div>
              </div>

              <div class="detail-row">
                <div class="detail-label">Module</div>
                <div class="detail-value">
                  @if($log->module)
                    <span class="badge badge-secondary">{{ ucfirst($log->module) }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </div>
              </div>

              <div class="detail-row">
                <div class="detail-label">Deskripsi</div>
                <div class="detail-value">{{ $log->description ?? '-' }}</div>
              </div>

              <div class="detail-row">
                <div class="detail-label">IP Address</div>
                <div class="detail-value text-monospace">{{ $log->ip_address ?? '-' }}</div>
              </div>

              <div class="detail-row">
                <div class="detail-label">User Agent</div>
                <div class="detail-value">
                  <small>{{ $log->user_agent ?? '-' }}</small>
                </div>
              </div>

              @if($log->data)
                <div class="detail-row">
                  <div class="detail-label">Data Tambahan</div>
                  <div class="detail-value">
                    <div class="json-viewer">
                      <pre class="mb-0">{{ json_encode($log->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                  </div>
                </div>
              @endif
            </div>
            <div class="card-footer">
              <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
              </a>
              <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                <i class="fas fa-trash mr-1"></i> Hapus Log
              </button>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card card-modern">
            <div class="card-header">
              <h3 class="card-title mb-0">Informasi Tambahan</h3>
            </div>
            <div class="card-body">
              <div class="detail-row">
                <div class="detail-label">Created At</div>
                <div class="detail-value">
                  <small>{{ $log->created_at->format('Y-m-d H:i:s') }}</small>
                </div>
              </div>

              <div class="detail-row">
                <div class="detail-label">Updated At</div>
                <div class="detail-value">
                  <small>{{ $log->updated_at->format('Y-m-d H:i:s') }}</small>
                </div>
              </div>

              @if($log->user)
                <div class="detail-row">
                  <div class="detail-label">User ID</div>
                  <div class="detail-value text-monospace">#{{ $log->user_id }}</div>
                </div>
              @endif
            </div>
          </div>

          @if($log->user)
            <div class="card card-modern">
              <div class="card-header">
                <h3 class="card-title mb-0">Aktivitas User Lainnya</h3>
              </div>
              <div class="card-body">
                @php
                  $recentLogs = \App\Models\Log::where('user_id', $log->user_id)
                    ->where('id', '!=', $log->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                @endphp

                @if($recentLogs->count() > 0)
                  <ul class="list-unstyled mb-0">
                    @foreach($recentLogs as $recentLog)
                      <li class="mb-2">
                        <a href="{{ route('logs.show', $recentLog->id) }}" class="text-decoration-none">
                          <small>
                            <strong>{{ ucfirst($recentLog->action) }}</strong>
                            @if($recentLog->module)
                              - {{ ucfirst($recentLog->module) }}
                            @endif
                            <br>
                            <span class="text-muted">{{ $recentLog->created_at->diffForHumans() }}</span>
                          </small>
                        </a>
                      </li>
                    @endforeach
                  </ul>
                  <a href="{{ route('logs.index', ['user_id' => $log->user_id]) }}" class="btn btn-sm btn-outline-primary btn-block mt-2">
                    Lihat Semua
                  </a>
                @else
                  <p class="text-muted mb-0"><small>Tidak ada aktivitas lain.</small></p>
                @endif
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </section>

  <!-- Delete Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
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
@endsection
