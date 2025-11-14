@extends('layouts.master')

@section('title','Sesi Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .card-modern .card-header { background:#fff; border-bottom: 1px solid #eef1f5; }
  .table-modern thead th { background:#f9fafb; color:#111827; font-weight:600; }
  .badge-status-draft { background: #6b7280; color: white; }
  .badge-status-aktif { background: #10b981; color: white; }
  .badge-status-selesai { background: #3b82f6; color: white; }
  .badge-status-dibatalkan { background: #ef4444; color: white; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Sesi Absensi</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Sesi Absensi</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    <!-- Filter Card -->
    <div class="card card-modern mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Filter</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('sesi-absensi.index') }}">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Kelas</label>
                <select name="kelas_id" class="form-control">
                  <option value="">Semua Kelas</option>
                  @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} - {{ $k->mataKuliah ? $k->mataKuliah->nama_mk : 'Mata Kuliah Tidak Ditemukan' }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                  <option value="">Semua Status</option>
                  <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                  <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                  <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">
                  <i class="fas fa-filter"></i> Filter
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Card -->
    <div class="card card-modern">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Sesi Absensi</h3>
        <a href="{{ route('sesi-absensi.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus mr-1"></i> Buat Sesi Absensi
        </a>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-modern table-hover">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Kelas</th>
                <th>Mata Kuliah</th>
                <th>Topik</th>
                <th>Waktu</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Statistik</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($sesiAbsensi as $sesi)
                <tr>
                  <td>
                    <strong>{{ $sesi->tanggal->format('d/m/Y') }}</strong><br>
                    <small class="text-muted">Pertemuan {{ $sesi->pertemuan_ke ?? '-' }}</small>
                  </td>
                  <td>{{ $sesi->kelas->nama_kelas }}</td>
                  <td>
                    <span class="text-monospace text-primary">{{ $sesi->kelas->mataKuliah ? $sesi->kelas->mataKuliah->kode_mk : '-' }}</span><br>
                    <small>{{ $sesi->kelas->mataKuliah ? $sesi->kelas->mataKuliah->nama_mk : 'Mata Kuliah Tidak Ditemukan' }}</small>
                  </td>
                  <td>{{ $sesi->topik ?? '-' }}</td>
                  <td>
                    <small>
                      <i class="far fa-clock"></i>
                      {{ $sesi->waktu_mulai->format('H:i') }} - {{ $sesi->waktu_selesai->format('H:i') }}
                    </small>
                  </td>
                  <td>
                    @if($sesi->metode == 'manual')
                      <span class="badge badge-secondary">Manual</span>
                    @elseif($sesi->metode == 'qr_code')
                      <span class="badge badge-info">QR Code</span>
                    @else
                      <span class="badge badge-warning">Geolocation</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge badge-status-{{ $sesi->status }}">{{ ucfirst($sesi->status) }}</span>
                  </td>
                  <td>
                    @php
                      $stats = $sesi->statistik;
                    @endphp
                    <small>
                      <span class="text-success">✓ {{ $stats['hadir'] }}</span> |
                      <span class="text-warning">I {{ $stats['izin'] }}</span> |
                      <span class="text-info">S {{ $stats['sakit'] }}</span> |
                      <span class="text-danger">A {{ $stats['alpha'] }}</span>
                    </small>
                  </td>
                  <td class="text-nowrap">
                    <a href="{{ route('sesi-absensi.show', $sesi->id) }}" class="btn btn-info btn-xs" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    @if($sesi->status == 'draft' || $sesi->status == 'aktif')
                      <a href="{{ route('sesi-absensi.edit', $sesi->id) }}" class="btn btn-warning btn-xs" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                    @endif
                    @if($sesi->status == 'aktif')
                      <form action="{{ route('sesi-absensi.close', $sesi->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-xs" title="Tutup Sesi" 
                                onclick="return confirm('Tutup sesi absensi ini?')">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                    @endif
                    <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" 
                            data-target="#deleteModal{{ $sesi->id }}" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $sesi->id }}" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                          </div>
                          <div class="modal-body">
                            Yakin ingin menghapus sesi absensi tanggal <strong>{{ $sesi->tanggal->format('d/m/Y') }}</strong>?
                            <br><small class="text-danger">Semua data absensi mahasiswa akan ikut terhapus.</small>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <form action="{{ route('sesi-absensi.destroy', $sesi->id) }}" method="POST" style="display:inline;">
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
                  <td colspan="9" class="text-center text-muted py-4">Belum ada sesi absensi</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if($sesiAbsensi->hasPages())
      <div class="card-footer">
        {{ $sesiAbsensi->appends(request()->query())->links() }}
      </div>
      @endif
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  $(function () {
    // Auto refresh untuk sesi yang aktif (optional)
    @if(request('status') == 'aktif')
      setTimeout(function() {
        location.reload();
      }, 60000); // Refresh setiap 1 menit
    @endif
  });
</script>
@endpush
