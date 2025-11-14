@extends('layouts.master')

@push('styles')
<style>
  /* Card */
  .data-card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.05); border:0; overflow:hidden; }
  .data-card .card-body { padding:1rem 1.25rem; }

  /* Modern table */
  .jadwal-table-wrapper .modern-table { border-collapse:separate; border-spacing:0 8px; }
  .jadwal-table-wrapper thead th { background:#F8FAFC; color:#374151; font-weight:600; border:none !important; padding:12px 16px; }
  .jadwal-table-wrapper thead th:first-child { border-radius:8px 0 0 8px; }
  .jadwal-table-wrapper thead th:last-child { border-radius:0 8px 8px 0; }
  .jadwal-table-wrapper tbody td { background:#fff; vertical-align:middle; padding:16px; border:none; }
  .jadwal-table-wrapper tbody tr { box-shadow:0 1px 3px rgba(0,0,0,.05); }
  .jadwal-table-wrapper tbody tr:hover td { background:#F8FAFC; }
  .jadwal-table-wrapper tbody td:first-child { border-radius:8px 0 0 8px; }
  .jadwal-table-wrapper tbody td:last-child { border-radius:0 8px 8px 0; }

  /* Actions */
  .jadwal-table-wrapper .table-actions .btn { border:1px solid #E5E7EB; background:#fff; color:#374151; }
  .jadwal-table-wrapper .table-actions .btn:hover { background:#F3F4F6; }
</style>
@endpush

@section('title', 'Jadwal Kuliah')
@section('page-title', 'Jadwal Kuliah')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Jadwal Kuliah</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('jadwal.create') }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-plus me-2"></i>Tambah Jadwal
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('jadwal.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Dosen</label>
                    <select name="dosen" class="form-select">
                        <option value="">Semua Dosen</option>
                        @foreach($dosenList as $dosen)
                            <option value="{{ $dosen->id }}" {{ request('dosen') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select name="mk" class="form-select">
                        <option value="">Semua MK</option>
                        @foreach($mkList as $mk)
                            <option value="{{ $mk->id }}" {{ request('mk') == $mk->id ? 'selected' : '' }}>
                                {{ $mk->kode_mk }} - {{ $mk->nama_mk }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kls)
                            <option value="{{ $kls->id }}" {{ request('kelas') == $kls->id ? 'selected' : '' }}>
                                {{ $kls->kode }} - {{ $kls->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hari</label>
                    <select name="hari" class="form-select">
                        <option value="">Semua Hari</option>
                        <option value="1" {{ request('hari') == '1' ? 'selected' : '' }}>Senin</option>
                        <option value="2" {{ request('hari') == '2' ? 'selected' : '' }}>Selasa</option>
                        <option value="3" {{ request('hari') == '3' ? 'selected' : '' }}>Rabu</option>
                        <option value="4" {{ request('hari') == '4' ? 'selected' : '' }}>Kamis</option>
                        <option value="5" {{ request('hari') == '5' ? 'selected' : '' }}>Jumat</option>
                        <option value="6" {{ request('hari') == '6' ? 'selected' : '' }}>Sabtu</option>
                        <option value="7" {{ request('hari') == '7' ? 'selected' : '' }}>Minggu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ruangan</label>
                    <select name="ruangan" class="form-select">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruanganList as $ruangan)
                            <option value="{{ $ruangan->id }}" {{ request('ruangan') == $ruangan->id ? 'selected' : '' }}>
                                {{ $ruangan->kode }}
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
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('jadwal.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive jadwal-table-wrapper">
            <table class="table modern-table align-middle text-start">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Kelas</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $index => $jadwal)
                    <tr>
                        <td class="fw-medium">{{ $jadwalList->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $jadwal->mataKuliah->kode_mk }}</strong><br>
                            <small class="text-muted">{{ $jadwal->mataKuliah->nama_mk }}</small>
                        </td>
                        <td>{{ $jadwal->dosen->name }}</td>
                        <td>{{ $jadwal->hari_nama }}</td>
                        <td>
                            <small>
                                {{ date('H:i', strtotime($jadwal->jam_mulai)) }} - 
                                {{ date('H:i', strtotime($jadwal->jam_selesai)) }}
                            </small>
                        </td>
                        <td>{{ $jadwal->ruangan->kode }}</td>
                        <td>
                            @if($jadwal->kelas)
                                <small>{{ $jadwal->kelas->nama }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $jadwal->mahasiswa->count() }}</td>
                        <td>
                            @if($jadwal->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('jadwal.show', $jadwal->id) }}" class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('jadwal.edit', $jadwal->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(this)" data-action="{{ route('jadwal.destroy', $jadwal->id) }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Tidak ada data jadwal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Menampilkan {{ $jadwalList->firstItem() ?? 0 }}-{{ $jadwalList->lastItem() ?? 0 }} dari {{ $jadwalList->total() }} data
            </div>
            <div>
                {{ $jadwalList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(btn) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Jadwal?',
            text: 'Semua pertemuan dan absensi terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                const action = btn.getAttribute('data-action');
                form.action = action;
                form.submit();
            }
        });
    } else {
        // Fallback if SweetAlert2 not available
        if (confirm('Yakin ingin menghapus jadwal ini? Semua pertemuan dan absensi terkait akan ikut terhapus.')) {
            const form = document.getElementById('delete-form');
            const action = btn.getAttribute('data-action');
            form.action = action;
            form.submit();
        }
    }
}

// Flash messages
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = @json(session('success'));
    const flashError = @json(session('error'));
    if (typeof Swal !== 'undefined') {
        if (flashSuccess) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: flashSuccess, confirmButtonColor: '#0e4a95' });
        } else if (flashError) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: flashError, confirmButtonColor: '#0e4a95' });
        }
    }
});
</script>
@endpush
@endsection
