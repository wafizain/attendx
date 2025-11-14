@extends('layouts.master')

@section('title', 'Data Ruangan')
@section('page-title', 'Data Ruangan')

@push('styles')
<style>
  /* Scoped to Ruangan index only */
  .ruang-table-wrapper .modern-table { border-collapse: separate; border-spacing: 0 8px; }
  .ruang-table-wrapper thead th { background:#F8FAFC; color:#374151; font-weight:600; border:none !important; padding:12px 16px; }
  .ruang-table-wrapper tbody tr { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); }
  .ruang-table-wrapper tbody tr:hover { box-shadow:0 4px 10px rgba(0,0,0,.08); transform: translateY(-1px); }
  .ruang-table-wrapper tbody td { border:none !important; padding:14px 16px; vertical-align: middle; }
  .ruang-table-wrapper tbody tr td:first-child { border-top-left-radius:12px; border-bottom-left-radius:12px; }
  .ruang-table-wrapper tbody tr td:last-child { border-top-right-radius:12px; border-bottom-right-radius:12px; }
  .ruang-table-wrapper .table-actions .btn { border:1px solid #E5E7EB; background:#fff; color:#374151; }
  .ruang-table-wrapper .table-actions .btn:hover { background:#F3F4F6; }
  .ruang-table-wrapper .card-header { background:#fff; border-bottom:1px solid #E5E7EB; font-weight:600; }
</style>
@endpush

@section('content')
 

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Ruangan</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importRuanganModal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <a href="{{ route('ruangan.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Ruangan
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('ruangan.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama ruangan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lokasi</label>
                    <select name="lokasi" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach($lokasiList as $lokasi)
                            <option value="{{ $lokasi }}" {{ request('lokasi') == $lokasi ? 'selected' : '' }}>
                                {{ $lokasi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('ruangan.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- Summary under filters -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Ruangan: <strong>{{ $stats['total'] }}</strong></div>
            
        </div>
        <!-- Table -->
        <div class="table-responsive ruang-table-wrapper">
                <table class="table modern-table align-middle">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Kode</th>
                            <th>Nama Ruangan</th>
                            <th>Kapasitas</th>
                            <th>Lokasi</th>
                            <th>Jadwal Aktif</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ruanganList as $index => $ruangan)
                        <tr>
                            <td>{{ $ruanganList->firstItem() + $index }}</td>
                            <td>{{ $ruangan->kode }}</td>
                            <td>{{ $ruangan->nama }}</td>
                            <td class="text-center">{{ $ruangan->kapasitas }}</td>
                            <td>{{ $ruangan->lokasi ?? '-' }}</td>
                            <td class="text-center">{{ $ruangan->jadwalKuliah()->where('status', 'aktif')->count() }}</span>
                            </td>
                            <td>
                                @if($ruangan->status == 'aktif')
                                    Aktif
                                @else
                                    Nonaktif
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm table-actions">
                                    <a href="{{ route('ruangan.show', $ruangan->id) }}" class="btn" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('ruangan.edit', $ruangan->id) }}" class="btn" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn" onclick="confirmDelete({{ $ruangan->id }}, '{{ $ruangan->nama }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Tidak ada data ruangan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
                Menampilkan {{ $ruanganList->firstItem() }} - {{ $ruanganList->lastItem() }} dari {{ $ruanganList->total() }} data
            </div>
            <div>
                {{ $ruanganList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        confirmButtonColor: '#0e4a95'
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: @json(session('error')),
        confirmButtonColor: '#0e4a95'
    });
    @endif
});

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Ruangan?',
        text: 'Yakin ingin menghapus ruangan "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("ruangan.destroy", ":id") }}'.replace(':id', id);
            
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
